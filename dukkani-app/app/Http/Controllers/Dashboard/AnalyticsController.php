<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Http\Controllers\Controller;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    private const STATUS_COLORS = [
        'new' => '#c9a15a',
        'confirmed' => '#5b8fb9',
        'in_production' => '#8b7bb8',
        'ready' => '#5fa8a0',
        'out_for_delivery' => '#d08a4e',
        'delivered' => '#6c7f65',
        'cancelled' => '#b3443a',
    ];

    public function __invoke(Request $request, AnalyticsService $analytics): View
    {
        $store = auth()->user()->store()->firstOrFail();

        $period = $request->integer('period', 30);
        $period = in_array($period, [7, 30, 90], true) ? $period : 30;

        $productSort = $request->string('sort')->toString();
        $productSort = in_array($productSort, ['revenue', 'sold'], true) ? $productSort : 'revenue';

        $from = today()->subDays($period - 1)->startOfDay();
        $to = today()->endOfDay();

        $statusCounts = $analytics->statusBreakdownBetween($store, $from, $to);
        $statusChart = ['labels' => [], 'values' => [], 'colors' => [], 'unit' => 'طلب'];

        foreach (OrderStatus::cases() as $status) {
            $count = $statusCounts[$status->value] ?? 0;

            if ($count === 0) {
                continue;
            }

            $statusChart['labels'][] = $status->label();
            $statusChart['values'][] = $count;
            $statusChart['colors'][] = self::STATUS_COLORS[$status->value];
        }

        $fulfillmentCounts = $analytics->fulfillmentSplitBetween($store, $from, $to);
        $fulfillmentChart = $this->splitChart(
            [
                FulfillmentType::Delivery->value => [FulfillmentType::Delivery->label(), '#b25c38'],
                FulfillmentType::Pickup->value => [FulfillmentType::Pickup->label(), '#6c7f65'],
            ],
            $fulfillmentCounts,
        );

        $paymentCounts = $analytics->paymentSplitBetween($store, $from, $to);
        $paymentChart = $this->splitChart(
            [
                PaymentMethod::Cash->value => [PaymentMethod::Cash->label(), '#6c7f65'],
                PaymentMethod::Cliq->value => [PaymentMethod::Cliq->label(), '#b25c38'],
                PaymentMethod::BankTransfer->value => [PaymentMethod::BankTransfer->label(), '#c9a15a'],
            ],
            $paymentCounts,
        );

        $weekdayChart = $analytics->weekdayLoadBetween($store, $from, $to);
        $peakValue = max($weekdayChart['values']);
        $peakIndex = $peakValue > 0 ? array_search($peakValue, $weekdayChart['values'], true) : null;
        $weekdayChart['colors'] = array_map(
            fn (int $value) => $value === $peakValue && $peakValue > 0 ? '#b25c38' : '#e7d8c8',
            $weekdayChart['values'],
        );

        return view('dashboard.analytics.index', [
            'store' => $store,
            'period' => $period,
            'periodOptions' => [7 => '7 أيام', 30 => '30 يوماً', 90 => '90 يوماً'],
            'rangeLabel' => $from->translatedFormat('j M').' — '.$to->translatedFormat('j M Y'),
            'summary' => $analytics->periodSummary($store, $from, $to),
            'revenueChart' => $analytics->revenueSeriesBetween($store, $from, $to),
            'statusChart' => $statusChart,
            'statusTotal' => array_sum($statusChart['values']),
            'fulfillmentChart' => $fulfillmentChart,
            'paymentChart' => $paymentChart,
            'weekdayChart' => $weekdayChart,
            'weekdayPeak' => $peakIndex === null
                ? null
                : ['label' => $weekdayChart['labels'][$peakIndex], 'value' => $peakValue],
            'topProducts' => $analytics->topProductsBetween($store, $from, $to, 5, $productSort),
            'productSort' => $productSort,
            'topCustomers' => $analytics->topCustomersBetween($store, $from, $to, 5),
        ]);
    }

    /**
     * @param  array<string, array{0: string, 1: string}>  $definitions
     * @param  array<string, int>  $counts
     */
    private function splitChart(array $definitions, array $counts): array
    {
        $chart = ['labels' => [], 'values' => [], 'colors' => [], 'unit' => 'طلب'];

        foreach ($definitions as $key => [$label, $color]) {
            $count = $counts[$key] ?? 0;

            if ($count === 0) {
                continue;
            }

            $chart['labels'][] = $label;
            $chart['values'][] = $count;
            $chart['colors'][] = $color;
        }

        return $chart;
    }
}
