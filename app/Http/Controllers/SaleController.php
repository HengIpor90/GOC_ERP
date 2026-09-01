<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->where('status', 'active')->orderBy('name')->get();
        $customers = Customer::query()->where('status', 'active')->orderBy('name')->get();
        $sales = Sale::query()
            ->with(['items.product', 'customer'])
            ->latest('sold_at')
            ->paginate(12);
        $salesStats = [
            'revenue' => Sale::query()->sum('total'),
            'today' => Sale::query()->whereDate('sold_at', today())->sum('total'),
            'transactions' => Sale::query()->count(),
            'units' => SaleItem::query()->sum('quantity'),
        ];
        $paymentBanks = config('payment_banks', []);

        return view('sales.index', compact('products', 'customers', 'sales', 'salesStats', 'paymentBanks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $bankKeys = array_keys(config('payment_banks', ['aba' => []]));

        if (! $request->has('items') && $request->filled('product_id')) {
            $request->merge([
                'items' => [[
                    'product_id' => $request->input('product_id'),
                    'sale_type' => $request->input('sale_type', 'general'),
                    'quantity' => $request->input('quantity'),
                    'units_per_pack' => $request->input('units_per_pack', 1),
                    'unit_price' => $request->input('unit_price'),
                    'discount_rate' => $request->input('discount_rate', 0),
                ]],
            ]);
        }

        $request->merge([
            'items' => collect($request->input('items', []))->map(function ($item): array {
                $item = is_array($item) ? $item : [];

                return array_merge([
                    'sale_type' => 'general',
                    'units_per_pack' => 1,
                    'discount_rate' => 0,
                ], $item);
            })->all(),
        ]);

        $validated = $request->validate([
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'payment_method' => ['required', 'string', Rule::in($bankKeys)],
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.sale_type' => ['required', Rule::in(['general', 'retail', 'wholesale'])],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'items.*.units_per_pack' => ['required', 'integer', 'min:1', 'max:4294967295'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'items.*.discount_rate' => ['required', 'numeric', 'min:0', 'max:100'],
        ], [], [
            'items.*.product_id' => 'product',
            'items.*.sale_type' => 'sale type',
            'items.*.quantity' => 'quantity',
            'items.*.units_per_pack' => 'units per pack',
            'items.*.unit_price' => 'unit price',
            'items.*.discount_rate' => 'discount',
        ]);

        DB::transaction(function () use ($validated, $request): void {
            $productIds = collect($validated['items'])
                ->pluck('product_id')
                ->map(fn ($id): int => (int) $id)
                ->all();

            $products = Product::query()
                ->whereKey($productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $items = collect($validated['items'])->map(function (array $item) use ($products): array {
                $product = $products->get((int) $item['product_id']);

                if (! $product instanceof Product) {
                    throw ValidationException::withMessages([
                        'items' => 'One of the selected products is no longer available.',
                    ]);
                }

                $saleType = $item['sale_type'];
                $saleQuantity = (int) $item['quantity'];
                $unitsPerPack = $saleType === 'wholesale' ? (int) $item['units_per_pack'] : 1;
                $stockQuantity = $saleQuantity * $unitsPerPack;

                if ($stockQuantity > 4294967295) {
                    throw ValidationException::withMessages([
                        'items' => "The calculated stock quantity is too large for {$product->name}.",
                    ]);
                }

                if ($product->stock < $stockQuantity) {
                    throw ValidationException::withMessages([
                        'items' => "Only {$product->stock} units are available for {$product->name}.",
                    ]);
                }

                $customPrice = $item['unit_price'] ?? null;
                $unitPrice = $customPrice === null || $customPrice === ''
                    ? (float) $product->price * $unitsPerPack
                    : (float) $customPrice;
                $discountRate = (float) $item['discount_rate'];
                $subtotal = round($unitPrice * $saleQuantity, 2);

                if ($subtotal > 9999999999.99) {
                    throw ValidationException::withMessages([
                        'items' => "The line subtotal is too large for {$product->name}.",
                    ]);
                }

                return [
                    'product' => $product,
                    'product_id' => (int) $product->getKey(),
                    'sale_type' => $saleType,
                    'sale_quantity' => $saleQuantity,
                    'units_per_pack' => $unitsPerPack,
                    'quantity' => $stockQuantity,
                    'unit_price' => $unitPrice,
                    'discount_rate' => $discountRate,
                    'subtotal' => $subtotal,
                    'total' => round($subtotal * (1 - ($discountRate / 100)), 2),
                ];
            });

            $firstItem = $items->first();

            if (! is_array($firstItem)) {
                throw ValidationException::withMessages(['items' => 'Add at least one product.']);
            }

            $grandTotal = round((float) $items->sum('total'), 2);

            if ($grandTotal > 9999999999.99) {
                throw ValidationException::withMessages(['items' => 'The invoice total is too large.']);
            }

            $sale = Sale::query()->create([
                'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'product_id' => $firstItem['product_id'],
                'customer_id' => $validated['customer_id'] ?? null,
                'quantity' => $firstItem['quantity'],
                'unit_price' => $firstItem['unit_price'],
                'total' => $grandTotal,
                'payment_method' => $validated['payment_method'],
                'sold_at' => now(),
                'created_by' => $request->user()?->getKey(),
            ]);

            $sale->items()->createMany($items->map(fn (array $item): array => [
                'product_id' => $item['product_id'],
                'sale_type' => $item['sale_type'],
                'sale_quantity' => $item['sale_quantity'],
                'units_per_pack' => $item['units_per_pack'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_rate' => $item['discount_rate'],
                'subtotal' => $item['subtotal'],
                'total' => $item['total'],
            ])->all());

            $items->each(function (array $item): void {
                $item['product']->decrement('stock', $item['quantity']);
            });
        });

        return redirect()->route('sales.index')->with('success', 'Invoice recorded and all product stocks updated.');
    }

    public function invoice(Sale $sale): View
    {
        $sale->load(['items.product', 'customer', 'creator']);
        $bank = $sale->paymentBank();

        return view('sales.invoice', compact('sale', 'bank'));
    }

    public function printAll(Request $request): View
    {
        $filters = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $sales = Sale::query()
            ->with(['items.product', 'customer', 'creator'])
            ->when(
                $filters['from'] ?? null,
                fn ($query, $from) => $query->whereDate('sold_at', '>=', $from),
            )
            ->when(
                $filters['to'] ?? null,
                fn ($query, $to) => $query->whereDate('sold_at', '<=', $to),
            )
            ->oldest('sold_at')
            ->get();

        $summary = [
            'transactions' => $sales->count(),
            'units' => (int) $sales->sum(fn (Sale $sale): int => (int) $sale->items->sum('quantity')),
            'revenue' => (float) $sales->sum('total'),
        ];

        $paymentTotals = $sales
            ->groupBy(fn (Sale $sale): string => strtoupper($sale->payment_method ?: 'ABA'))
            ->map(fn ($items): array => [
                'transactions' => $items->count(),
                'total' => (float) $items->sum('total'),
            ]);

        return view('sales.print-all', compact('sales', 'summary', 'paymentTotals', 'filters'));
    }

    public function destroy(Sale $sale): RedirectResponse
    {
        DB::transaction(function () use ($sale): void {
            $lockedSale = Sale::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($sale->getKey());
            $products = Product::query()
                ->whereKey($lockedSale->items->pluck('product_id')->all())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            foreach ($lockedSale->items as $item) {
                $products->get((int) $item->product_id)?->increment('stock', $item->quantity);
            }

            $lockedSale->delete();
        });

        return redirect()->route('sales.index')->with('success', 'Invoice deleted and all product stocks restored.');
    }
}
