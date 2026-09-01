<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            $monthExpr = "strftime('%Y-%m', sold_at)";
        } elseif ($driver === 'pgsql') {
            $monthExpr = "to_char(sold_at, 'YYYY-MM')";
        } else {
            $monthExpr = "DATE_FORMAT(sold_at, '%Y-%m')";
        }

        $monthly = Sale::query()
            ->selectRaw("{$monthExpr} as ym, SUM(total) as revenue")
            ->groupBy('ym')
            ->orderBy('ym')
            ->limit(12)
            ->pluck('revenue')
            ->map(fn ($v) => (float) $v)
            ->values();

        $max = max($monthly->max() ?: 0, 1.0);
        $barHeights = $monthly
            ->map(fn ($v) => max(8, (int) round(($v / $max) * 100)))
            ->all();

        while (count($barHeights) < 12) {
            array_unshift($barHeights, 8);
        }
        $barHeights = array_slice($barHeights, -12);

        $growth = null;
        if ($monthly->count() >= 2) {
            $prev = (float) $monthly[$monthly->count() - 2];
            $curr = (float) $monthly[$monthly->count() - 1];
            if ($prev > 0) {
                $growth = round((($curr - $prev) / $prev) * 100, 1);
            }
        }

        $homeStats = [
            'products' => Product::query()->count(),
            'stock' => (int) Product::query()->sum('stock'),
            'sales' => (float) Sale::query()->sum('total'),
            'low_stock' => Product::query()->where('stock', '<=', 5)->count(),
            'pending_orders' => Order::query()->where('status', 'pending')->count(),
            'bars' => $barHeights,
            'growth' => $growth,
        ];

        return view('welcome', compact('homeStats'));
    }
}