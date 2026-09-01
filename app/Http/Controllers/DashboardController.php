<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $dashboardStats = [
            'products' => Product::query()->count(),
            'stock' => Product::query()->sum('stock'),
            'sales_today' => Sale::query()->whereDate('sold_at', today())->sum('total'),
            'pending_orders' => Order::query()->whereIn('status', ['pending', 'processing'])->count(),
        ];

        $recentSales = Sale::query()
            ->with(['items.product', 'customer'])
            ->latest('sold_at')
            ->limit(5)
            ->get();

        $lowStockProducts = Product::query()
            ->where('status', 'active')
            ->where('stock', '<=', 5)
            ->orderBy('stock')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact('dashboardStats', 'recentSales', 'lowStockProducts'));
    }
}
