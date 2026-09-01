<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('q', ''));
        $status = (string) $request->input('status', 'all');

        if (! in_array($status, ['all', 'active', 'inactive'], true)) {
            $status = 'all';
        }

        $customers = Customer::query()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            })
            ->when($status !== 'all', fn (Builder $query) => $query->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $customerStats = [
            'total' => Customer::query()->count(),
            'active' => Customer::query()->where('status', 'active')->count(),
            'inactive' => Customer::query()->where('status', 'inactive')->count(),
            'new_month' => Customer::query()->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];

        return view('customers.index', compact('customers', 'customerStats', 'search', 'status'));
    }

    public function store(Request $request): RedirectResponse
    {
        Customer::query()->create($this->validatedCustomer($request));

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    public function update(Request $request, Customer $customer): RedirectResponse
    {
        $customer->update($this->validatedCustomer($request, $customer));

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    /** @return array<string, mixed> */
    private function validatedCustomer(Request $request, ?Customer $customer = null): array
    {
        $uniqueEmail = Rule::unique('customers', 'email');

        if ($customer !== null) {
            $uniqueEmail->ignore($customer);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['nullable', 'email', 'max:150', $uniqueEmail],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:500'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);
    }
}
