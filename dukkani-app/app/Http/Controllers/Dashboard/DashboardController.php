<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(AnalyticsService $analytics): View
    {
        $store = auth()->user()->store()->with('hours')->firstOrFail();

        $kpis = $analytics->kpis($store);

        $todayOrders = $store->orders()
            ->with(['customer', 'items'])
            ->whereDate('scheduled_at', today())
            ->where('status', '!=', 'cancelled')
            ->orderBy('scheduled_at')
            ->get();

        $recentOrders = $store->orders()
            ->with('customer')
            ->latest()
            ->take(7)
            ->get();

        $notifications = $store->notifications()->latest()->take(5)->get();

        return view('dashboard.index', [
            'store' => $store,
            'kpis' => $kpis,
            'todayOrders' => $todayOrders,
            'recentOrders' => $recentOrders,
            'notifications' => $notifications,
            'chart' => $analytics->revenueSeries($store, 14),
            'topProducts' => $analytics->topProducts($store, 4),
        ]);
    }
}
