<?php

namespace App\Services;

use App\Enums\DepositStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    public function kpis(Store $store): array
    {
        $monthStart = now()->startOfMonth();
        $lastMonthStart = now()->subMonthNoOverflow()->startOfMonth();
        $lastMonthEnd = now()->subMonthNoOverflow()->endOfMonth();

        $revenue = (int) Order::forStore($store)->where('status', OrderStatus::Delivered)
            ->whereDate('created_at', '>=', $monthStart)->sum('total_fils');

        $lastRevenue = (int) Order::forStore($store)->where('status', OrderStatus::Delivered)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->sum('total_fils');

        $ordersThisMonth = Order::forStore($store)->where('status', '!=', OrderStatus::Cancelled)
            ->whereDate('created_at', '>=', $monthStart)->count();

        $lastOrders = Order::forStore($store)->where('status', '!=', OrderStatus::Cancelled)
            ->whereBetween('created_at', [$lastMonthStart, $lastMonthEnd])->count();

        $pending = Order::forStore($store)->whereIn('status', [
            OrderStatus::New, OrderStatus::Confirmed, OrderStatus::InProduction, OrderStatus::Ready, OrderStatus::OutForDelivery,
        ])->count();

        $today = Order::forStore($store)->whereDate('scheduled_at', today())
            ->where('status', '!=', OrderStatus::Cancelled)->count();

        $newCustomers = $store->customers()->whereDate('created_at', '>=', $monthStart)->count();

        $outstanding = (int) Order::forStore($store)
            ->where('deposit_status', DepositStatus::Pending)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->sum('deposit_fils');

        $avg = $ordersThisMonth > 0 ? (int) round($revenue / $ordersThisMonth) : 0;

        return [
            'revenue' => $revenue,
            'revenue_change' => $this->change($revenue, $lastRevenue),
            'orders' => $ordersThisMonth,
            'orders_change' => $this->change($ordersThisMonth, $lastOrders),
            'pending' => $pending,
            'today' => $today,
            'new_customers' => $newCustomers,
            'outstanding_deposits' => $outstanding,
            'avg_order' => $avg,
        ];
    }

    public function revenueSeries(Store $store, int $days = 14): array
    {
        return $this->revenueSeriesBetween(
            $store,
            today()->subDays($days - 1)->startOfDay(),
            today()->endOfDay(),
        );
    }

    public function revenueSeriesBetween(Store $store, Carbon $from, Carbon $to): array
    {
        $start = $from->copy()->startOfDay();
        $end = $to->copy()->startOfDay();

        $rows = Order::forStore($store)
            ->where('status', OrderStatus::Delivered)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('DATE(created_at) as day, SUM(total_fils) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $labels = [];
        $values = [];

        for ($day = $start->copy(); $day->lte($end); $day->addDay()) {
            $labels[] = $day->translatedFormat('j M');
            $values[] = (int) ($rows[$day->toDateString()] ?? 0) / 1000;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    public function periodSummary(Store $store, Carbon $from, Carbon $to): array
    {
        $current = $this->periodTotals($store, $from, $to);

        $days = (int) $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;
        $previousTo = $from->copy()->subSecond();
        $previousFrom = $previousTo->copy()->subDays($days - 1)->startOfDay();
        $previous = $this->periodTotals($store, $previousFrom, $previousTo);

        $avg = $current['delivered'] > 0 ? intdiv($current['revenue'], $current['delivered']) : 0;
        $previousAvg = $previous['delivered'] > 0 ? intdiv($previous['revenue'], $previous['delivered']) : 0;
        $finalized = $current['delivered'] + $current['cancelled'];

        return [
            'revenue' => $current['revenue'],
            'revenue_change' => $this->change($current['revenue'], $previous['revenue']),
            'orders' => $current['orders'],
            'orders_change' => $this->change($current['orders'], $previous['orders']),
            'avg_order' => $avg,
            'avg_order_change' => $this->change($avg, $previousAvg),
            'delivered' => $current['delivered'],
            'cancelled' => $current['cancelled'],
            'completion_rate' => $finalized > 0 ? (int) round($current['delivered'] / $finalized * 100) : 0,
            'items_sold' => $current['items_sold'],
            'new_customers' => $current['new_customers'],
            'unique_customers' => $current['unique_customers'],
            'repeat_rate' => $this->repeatRate($store),
        ];
    }

    protected function periodTotals(Store $store, Carbon $from, Carbon $to): array
    {
        $orders = fn () => Order::forStore($store)->whereBetween('created_at', [$from, $to]);

        $revenue = (int) $orders()->where('status', OrderStatus::Delivered)->sum('total_fils');
        $delivered = $orders()->where('status', OrderStatus::Delivered)->count();
        $cancelled = $orders()->where('status', OrderStatus::Cancelled)->count();
        $ordersCount = $orders()->where('status', '!=', OrderStatus::Cancelled)->count();
        $uniqueCustomers = $orders()->where('status', '!=', OrderStatus::Cancelled)->distinct()->count('customer_id');

        $itemsSold = (int) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.store_id', $store->id)
            ->where('orders.status', OrderStatus::Delivered->value)
            ->whereBetween('orders.created_at', [$from, $to])
            ->sum('order_items.quantity');

        return [
            'revenue' => $revenue,
            'delivered' => $delivered,
            'cancelled' => $cancelled,
            'orders' => $ordersCount,
            'unique_customers' => $uniqueCustomers,
            'items_sold' => $itemsSold,
            'new_customers' => $store->customers()->whereBetween('created_at', [$from, $to])->count(),
        ];
    }

    public function statusBreakdown(Store $store): array
    {
        return Order::forStore($store)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->status->value => (int) $row->total])
            ->all();
    }

    public function statusBreakdownBetween(Store $store, Carbon $from, Carbon $to): array
    {
        return Order::forStore($store)
            ->whereBetween('created_at', [$from, $to])
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->status->value => (int) $row->total])
            ->all();
    }

    public function topProducts(Store $store, int $limit = 5): array
    {
        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.store_id', $store->id)
            ->where('orders.status', OrderStatus::Delivered->value)
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as sold'), DB::raw('SUM(order_items.line_total_fils) as revenue'))
            ->groupBy('order_items.product_name')
            ->orderByDesc('sold')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    public function topProductsBetween(Store $store, Carbon $from, Carbon $to, int $limit = 5, string $sort = 'revenue'): array
    {
        $column = $sort === 'sold' ? 'sold' : 'revenue';

        return DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.store_id', $store->id)
            ->where('orders.status', OrderStatus::Delivered->value)
            ->whereBetween('orders.created_at', [$from, $to])
            ->select('order_items.product_name', DB::raw('SUM(order_items.quantity) as sold'), DB::raw('SUM(order_items.line_total_fils) as revenue'))
            ->groupBy('order_items.product_name')
            ->orderByDesc($column)
            ->limit($limit)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    public function fulfillmentSplit(Store $store): array
    {
        return Order::forStore($store)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->select('fulfillment_type', DB::raw('COUNT(*) as total'))
            ->groupBy('fulfillment_type')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->fulfillment_type->value => (int) $row->total])
            ->all();
    }

    public function fulfillmentSplitBetween(Store $store, Carbon $from, Carbon $to): array
    {
        return Order::forStore($store)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->whereBetween('created_at', [$from, $to])
            ->select('fulfillment_type', DB::raw('COUNT(*) as total'))
            ->groupBy('fulfillment_type')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->fulfillment_type->value => (int) $row->total])
            ->all();
    }

    public function paymentSplitBetween(Store $store, Carbon $from, Carbon $to): array
    {
        return Order::forStore($store)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->whereBetween('created_at', [$from, $to])
            ->select('payment_method', DB::raw('COUNT(*) as total'))
            ->groupBy('payment_method')
            ->get()
            ->mapWithKeys(fn ($row) => [($row->payment_method?->value ?? 'cash') => (int) $row->total])
            ->all();
    }

    public function weekdayLoadBetween(Store $store, Carbon $from, Carbon $to): array
    {
        $labels = ['الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة', 'السبت'];
        $values = array_fill(0, 7, 0);

        Order::forStore($store)
            ->where('status', '!=', OrderStatus::Cancelled)
            ->whereBetween('scheduled_at', [$from, $to])
            ->select('scheduled_at')
            ->get()
            ->each(function (Order $order) use (&$values) {
                $values[(int) $order->scheduled_at->dayOfWeek]++;
            });

        return ['labels' => $labels, 'values' => $values];
    }

    public function topCustomersBetween(Store $store, Carbon $from, Carbon $to, int $limit = 5): array
    {
        return DB::table('orders')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->where('orders.store_id', $store->id)
            ->where('orders.status', OrderStatus::Delivered->value)
            ->whereBetween('orders.created_at', [$from, $to])
            ->select('customers.id', 'customers.name', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(orders.total_fils) as spent'))
            ->groupBy('customers.id', 'customers.name')
            ->orderByDesc('spent')
            ->limit($limit)
            ->get()
            ->map(fn ($row) => (array) $row)
            ->all();
    }

    public function repeatRate(Store $store): int
    {
        $total = $store->customers()->has('orders')->count();

        if ($total === 0) {
            return 0;
        }

        $repeat = $store->customers()->has('orders', '>', 1)->count();

        return (int) round($repeat / $total * 100);
    }

    public function monthlyRevenue(Store $store, int $months = 6): array
    {
        $labels = [];
        $values = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = now()->subMonthsNoOverflow($i);
            $labels[] = $month->translatedFormat('F');
            $values[] = (int) Order::forStore($store)
                ->where('status', OrderStatus::Delivered)
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('total_fils') / 1000;
        }

        return ['labels' => $labels, 'values' => $values];
    }

    protected function change(int|float $current, int|float $previous): float
    {
        if ($previous <= 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round(($current - $previous) / $previous * 100, 1);
    }
}
