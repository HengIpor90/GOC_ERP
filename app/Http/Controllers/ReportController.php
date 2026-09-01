<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $inventoryValue = Product::query()
            ->get(['price', 'stock'])
            ->sum(fn (Product $product): float => (float) $product->price * $product->stock);

        $salesRevenue = (float) Sale::query()->sum('total');
        $ordersRevenue = (float) Order::query()->sum('total');
        $completedOrdersRevenue = (float) Order::query()
            ->where('status', 'completed')
            ->sum('total');

        $monthSales = (float) Sale::query()
            ->whereMonth('sold_at', now()->month)
            ->whereYear('sold_at', now()->year)
            ->sum('total');

        $monthOrders = (float) Order::query()
            ->whereMonth('ordered_at', now()->month)
            ->whereYear('ordered_at', now()->year)
            ->sum('total');

        $reportStats = [
            'sales_revenue' => $salesRevenue,
            'orders_revenue' => $ordersRevenue,
            'completed_orders_revenue' => $completedOrdersRevenue,
            'total_revenue' => $salesRevenue + $ordersRevenue,
            'month_sales' => $monthSales,
            'month_orders' => $monthOrders,
            'month_revenue' => $monthSales + $monthOrders,
            'revenue' => $salesRevenue + $ordersRevenue,
            'inventory_value' => $inventoryValue,
            'customers' => Customer::query()->count(),
        ];

        $lowStockProducts = Product::query()
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->limit(10)
            ->get();

        $topProducts = SaleItem::query()
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as units_sold'),
                DB::raw('SUM(total) as revenue')
            )
            ->with('product')
            ->groupBy('product_id')
            ->orderByDesc('units_sold')
            ->limit(10)
            ->get();

        $recentSales = Sale::query()
            ->with(['items.product', 'customer'])
            ->latest('sold_at')
            ->limit(8)
            ->get();

        $recentOrders = Order::query()
            ->with(['items.product', 'customer'])
            ->latest('ordered_at')
            ->limit(8)
            ->get();

        $orderSummary = Order::query()
            ->select(
                'status',
                DB::raw('COUNT(*) as total'),
                DB::raw('COALESCE(SUM(total), 0) as amount')
            )
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return view('reports.index', compact(
            'reportStats',
            'lowStockProducts',
            'topProducts',
            'recentSales',
            'recentOrders',
            'orderSummary',
        ));
    }
}
