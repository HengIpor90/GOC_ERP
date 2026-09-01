<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(): View
    {
        $products = Product::query()->where('status', 'active')->orderBy('name')->get();
        $customers = Customer::query()->where('status', 'active')->orderBy('name')->get();
        $orders = Order::query()->with(['items.product', 'customer'])->latest('ordered_at')->paginate(12);
        $orderStats = [
            'pending' => Order::query()->where('status', 'pending')->count(),
            'processing' => Order::query()->where('status', 'processing')->count(),
            'completed' => Order::query()->where('status', 'completed')->count(),
            'total' => Order::query()->sum('total'),
        ];
        $paymentBanks = config('payment_banks', []);

        return view('orders.index', compact('products', 'customers', 'orders', 'orderStats', 'paymentBanks'));
    }

    public function store(Request $request): RedirectResponse
    {
        $bankKeys = array_keys(config('payment_banks', ['aba' => []]));

        $request->merge([
            'payment_status' => $request->input('payment_status', 'unpaid'),
        ]);

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
            'payment_status' => ['required', Rule::in(['paid', 'unpaid'])],
            'status' => ['required', Rule::in(['pending', 'processing', 'completed', 'cancelled'])],
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

            $items = $this->prepareItems($validated['items'], $products);
            $firstItem = $items->first();

            if (! is_array($firstItem)) {
                throw ValidationException::withMessages(['items' => 'Add at least one product.']);
            }

            $grandTotal = round((float) $items->sum('total'), 2);

            if ($grandTotal > 9999999999.99) {
                throw ValidationException::withMessages(['items' => 'The order total is too large.']);
            }

            $stockApplied = $validated['status'] === 'completed';

            if ($stockApplied) {
                $this->ensureStockIsAvailable($items);
                $items->each(fn (array $item) => $item['product']->decrement('stock', $item['quantity']));
            }

            $order = Order::query()->create([
                'order_number' => 'ORD-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'product_id' => $firstItem['product_id'],
                'customer_id' => $validated['customer_id'] ?? null,
                'quantity' => $firstItem['quantity'],
                'unit_price' => $firstItem['unit_price'],
                'total' => $grandTotal,
                'payment_method' => $validated['payment_method'],
                'payment_status' => $validated['payment_status'],
                'status' => $validated['status'],
                'stock_applied' => $stockApplied,
                'ordered_at' => now(),
                'created_by' => $request->user()?->getKey(),
            ]);

            $order->items()->createMany($items->map(fn (array $item): array => [
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
        });

        return redirect()->route('orders.index')->with('success', 'Order created successfully.');
    }

    public function invoice(Order $order): View
    {
        $order->load(['items.product', 'customer', 'creator']);
        $bank = $order->paymentBank();

        return view('orders.invoice', compact('order', 'bank'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'processing', 'completed', 'cancelled'])],
            'payment_status' => ['nullable', Rule::in(['paid', 'unpaid'])],
        ]);

        DB::transaction(function () use ($validated, $order): void {
            $lockedOrder = Order::query()->with('items')->lockForUpdate()->findOrFail($order->getKey());
            $products = Product::query()
                ->whereKey($lockedOrder->items->pluck('product_id')->all())
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $items = $lockedOrder->items->map(function ($item) use ($products): array {
                $product = $products->get((int) $item->product_id);

                if (! $product instanceof Product) {
                    throw ValidationException::withMessages(['status' => 'One of the order products is unavailable.']);
                }

                return ['product' => $product, 'quantity' => (int) $item->quantity];
            });
            $newStatus = $validated['status'];

            if ($newStatus === 'completed' && ! $lockedOrder->stock_applied) {
                $this->ensureStockIsAvailable($items);
                $items->each(fn (array $item) => $item['product']->decrement('stock', $item['quantity']));
                $lockedOrder->stock_applied = true;
            } elseif ($newStatus !== 'completed' && $lockedOrder->stock_applied) {
                $items->each(fn (array $item) => $item['product']->increment('stock', $item['quantity']));
                $lockedOrder->stock_applied = false;
            }

            $lockedOrder->status = $newStatus;
            $lockedOrder->payment_status = $validated['payment_status'] ?? $lockedOrder->payment_status;
            $lockedOrder->save();
        });

        return redirect()->route('orders.index')->with('success', 'Order and payment status updated.');
    }

    public function destroy(Order $order): RedirectResponse
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->with('items')->lockForUpdate()->findOrFail($order->getKey());

            if ($lockedOrder->stock_applied) {
                $products = Product::query()
                    ->whereKey($lockedOrder->items->pluck('product_id')->all())
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($lockedOrder->items as $item) {
                    $products->get((int) $item->product_id)?->increment('stock', $item->quantity);
                }
            }

            $lockedOrder->delete();
        });

        return redirect()->route('orders.index')->with('success', 'Order deleted and stock restored when applicable.');
    }

    public function downloadInvoice(Order $order)
    {
        $order->load(['items.product', 'customer', 'creator']);
        $bank = $order->paymentBank();

        return Pdf::loadView('orders.invoice', compact('order', 'bank'))
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('defaultFont', 'DejaVu Sans')
            ->download('invoice-'.$order->order_number.'.pdf');
    }

    private function prepareItems(array $submittedItems, $products)
    {
        return collect($submittedItems)->map(function (array $item) use ($products): array {
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
    }

    private function ensureStockIsAvailable($items): void
    {
        foreach ($items as $item) {
            if ($item['product']->stock < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => "Only {$item['product']->stock} units are available for {$item['product']->name}.",
                ]);
            }
        }
    }
}
