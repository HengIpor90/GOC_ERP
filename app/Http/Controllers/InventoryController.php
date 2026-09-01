<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->where('status', 'active')->orderBy('name')->get();
        $movements = StockMovement::query()->with(['product', 'creator'])->latest()->paginate(12);
        $inventoryStats = [
            'stock' => Product::query()->sum('stock'),
            'received' => StockMovement::query()->where('type', 'receive')->sum('quantity'),
            'issued' => StockMovement::query()->where('type', 'issue')->sum('quantity'),
            'low_stock' => Product::query()->where('stock', '<=', 5)->count(),
        ];

        return view('inventory.index', compact('products', 'movements', 'inventoryStats'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type' => ['required', Rule::in(['receive', 'issue'])],
            'quantity' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $product = Product::query()->lockForUpdate()->findOrFail($validated['product_id']);
            $quantity = (int) $validated['quantity'];

            if ($validated['type'] === 'issue' && $product->stock < $quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Only {$product->stock} units are available for {$product->name}.",
                ]);
            }

            $validated['type'] === 'receive'
                ? $product->increment('stock', $quantity)
                : $product->decrement('stock', $quantity);

            StockMovement::query()->create([
                ...$validated,
                'created_by' => $request->user()?->getKey(),
            ]);
        });

        return redirect()->route('inventory.index')->with('success', 'Stock movement saved successfully.');
    }
}
