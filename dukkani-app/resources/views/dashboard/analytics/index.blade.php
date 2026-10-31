@extends('layouts.dashboard')

@section('title', 'التحليلات')
@section('page-title', 'التحليلات')

@section('content')
    @php
        use App\Support\Money;

        $productMetric = $productSort === 'sold' ? 'sold' : 'revenue';
        $maxProductMetric = max(1, (float) (collect($topProducts)->max($productMetric) ?? 1));
        $maxCustomerSpent = max(1, (float) (collect($topCustomers)->max('spent') ?? 1));
        $revenueTotal = (int) round(collect($revenueChart['values'])->sum() * 1000);
        $dailyAverage = (int) round($revenueTotal / max(1, $period));
    @endphp

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <p class="text-[11px] font-medium text-muted">{{ $rangeLabel }}</p>
            <h1 class="mt-1.5 font-display text-[2rem] font-bold leading-tight text-ink sm:text-[2.35rem]">التحليلات</h1>
            <p class="mt-2 max-w-xl text-sm leading-6 text-muted">
                صورة واضحة عن مبيعاتك، عملائك، وأوقات الذروة — حتى تخطّطي للمناسبات القادمة بثقة.
            </p>
        </div>

        <div class="inline-flex self-start rounded-full border border-line bg-surface p-1 shadow-[var(--shadow-soft)] lg:self-auto">
            @foreach ($periodOptions as $days => $label)
                <a
                    href="{{ route('analytics.index', ['period' => $days, 'sort' => $productSort]) }}"
                    @if ($period === $days) aria-current="page" @endif
                    class="rounded-full px-3.5 py-2 text-[11px] font-bold transition {{ $period === $days ? 'bg-brand text-white shadow-[0_8px_18px_-12px_rgba(142,68,37,0.9)]' : 'text-muted hover:bg-canvas hover:text-ink' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card card-hover p-5">
            <div class="flex items-start justify-between">
                <span class="kpi-icon bg-brand-soft text-brand">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3"/></svg>
                </span>
                @include('dashboard.partials.change', ['value' => $summary['revenue_change'], 'positive' => $summary['revenue_change'] >= 0])
            </div>
            <p class="mt-5 text-[11px] font-bold text-muted">إيرادات الفترة</p>
            <p class="mt-1 font-display text-[1.7rem] font-bold tracking-tight text-ink">{{ Money::format($summary['revenue']) }}</p>
            <p class="mt-1 text-[10px] text-muted/70">{{ $summary['items_sold'] }} قطعة مباعة من الطلبات المسلّمة</p>
        </div>

        <div class="card card-hover p-5">
            <div class="flex items-start justify-between">
                <span class="kpi-icon bg-sage-soft text-sage">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                </span>
                @include('dashboard.partials.change', ['value' => $summary['orders_change'], 'positive' => $summary['orders_change'] >= 0])
            </div>
            <p class="mt-5 text-[11px] font-bold text-muted">طلبات الفترة</p>
            <p class="mt-1 font-display text-[1.7rem] font-bold tracking-tight text-ink">{{ $summary['orders'] }} <span class="font-sans text-xs font-medium text-muted">طلباً</span></p>
            <p class="mt-1 text-[10px] text-muted/70">{{ $summary['delivered'] }} مسلّمة • {{ $summary['cancelled'] }} ملغاة</p>
        </div>

        <div class="card card-hover p-5">
            <div class="flex items-start justify-between">
                <span class="kpi-icon bg-gold-soft text-[#a47b32]">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
                @include('dashboard.partials.change', ['value' => $summary['avg_order_change'], 'positive' => $summary['avg_order_change'] >= 0])
            </div>
            <p class="mt-5 text-[11px] font-bold text-muted">متوسط قيمة الطلب</p>
            <p class="mt-1 font-display text-[1.7rem] font-bold tracking-tight text-ink">{{ Money::format($summary['avg_order']) }}</p>
            <p class="mt-1 text-[10px] text-muted/70">لكل طلب مسلّم في الفترة</p>
        </div>

        <div class="card card-hover p-5">
            <div class="flex items-start justify-between">
                <span class="kpi-icon bg-violet-50 text-violet-600">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </span>
                <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[10px] font-bold text-violet-600">{{ $summary['repeat_rate'] }}% متكررون</span>
            </div>
            <p class="mt-5 text-[11px] font-bold text-muted">عملاء الفترة</p>
            <p class="mt-1 font-display text-[1.7rem] font-bold tracking-tight text-ink">{{ $summary['unique_customers'] }} <span class="font-sans text-xs font-medium text-muted">عميلاً</span></p>
            <p class="mt-1 text-[10px] text-muted/70">{{ $summary['new_customers'] }} عميلاً جديداً خلال الفترة</p>
        </div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <div class="card p-5 sm:p-6 xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-bold text-ink">حركة الإيرادات</h2>
                    <p class="mt-1 text-[11px] text-muted">الطلبات المسلّمة خلال {{ $periodOptions[$period] }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex items-center gap-1.5 text-[10px] font-medium text-muted">
                        <span class="size-2 rounded-full bg-brand"></span>
                        الإيرادات
                    </span>
                    <span class="rounded-full border border-line px-3 py-1 text-[10px] font-bold text-muted">{{ $rangeLabel }}</span>
                </div>
            </div>

            <div class="mt-6 h-[18rem] w-full">
                @if (array_sum($revenueChart['values']) > 0)
                    <canvas x-data x-init="window.Dukkani.revenueChart($refs.chart, @js($revenueChart))" x-ref="chart" class="size-full" role="img" aria-label="مخطط الإيرادات اليومية للفترة المحددة"></canvas>
                @else
                    <div class="flex h-full flex-col items-center justify-center rounded-2xl border border-dashed border-line text-center">
                        <span class="flex size-11 items-center justify-center rounded-full bg-canvas text-muted">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3v18h18"/><path stroke-linecap="round" stroke-linejoin="round" d="M7 15l4-4 3 3 5-6"/></svg>
                        </span>
                        <p class="mt-3 text-xs font-bold text-ink">لا توجد إيرادات مسلّمة في هذه الفترة</p>
                        <p class="mt-1 text-[10px] text-muted">جرّبي توسيع الفترة أو راجعي الطلبات قيد التنفيذ.</p>
                    </div>
                @endif
            </div>

            <div class="mt-4 grid gap-2 sm:grid-cols-3">
                <div class="rounded-2xl bg-canvas/70 px-4 py-3">
                    <p class="text-[10px] text-muted">إجمالي الفترة</p>
                    <p class="mt-1 text-xs font-bold text-ink">{{ Money::format($revenueTotal) }}</p>
                </div>
                <div class="rounded-2xl bg-canvas/70 px-4 py-3">
                    <p class="text-[10px] text-muted">المتوسط اليومي</p>
                    <p class="mt-1 text-xs font-bold text-ink">{{ Money::format($dailyAverage) }}</p>
                </div>
                <div class="rounded-2xl bg-canvas/70 px-4 py-3">
                    <p class="text-[10px] text-muted">نسبة الإتمام</p>
                    <p class="mt-1 text-xs font-bold text-ink">{{ $summary['completion_rate'] }}%</p>
                </div>
            </div>
        </div>

        <div class="card p-5 sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-sm font-bold text-ink">حالة الطلبات</h2>
                    <p class="mt-1 text-[11px] text-muted">{{ $statusTotal }} طلباً في الفترة</p>
                </div>
                <span class="flex size-9 items-center justify-center rounded-full bg-brand-soft text-brand">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 1 0 7.5 7.5h-7.5V6Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0 0 13.5 3v7.5Z"/></svg>
                </span>
            </div>

            @if ($statusTotal > 0)
                <div class="relative mx-auto mt-6 h-44 w-44">
                    <canvas x-data x-init="window.Dukkani.doughnutChart($refs.chart, @js($statusChart))" x-ref="chart" class="size-full" role="img" aria-label="توزيع الطلبات حسب الحالة"></canvas>
                    <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                        <span class="font-display text-[1.9rem] font-bold leading-none text-ink">{{ $statusTotal }}</span>
                        <span class="mt-1 text-[9px] font-bold text-muted">طلباً</span>
                    </div>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-x-3 gap-y-2">
                    @foreach ($statusChart['labels'] as $index => $label)
                        <div class="flex items-center gap-2">
                            <span class="size-2 shrink-0 rounded-full" style="background-color: {{ $statusChart['colors'][$index] }}"></span>
                            <span class="min-w-0 flex-1 truncate text-[10px] text-muted">{{ $label }}</span>
                            <span class="text-[10px] font-bold text-ink">{{ $statusChart['values'][$index] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="mt-6 flex h-44 flex-col items-center justify-center rounded-2xl border border-dashed border-line text-center">
                    <p class="text-xs font-bold text-ink">لا توجد طلبات في الفترة</p>
                    <p class="mt-1 text-[10px] text-muted">ستظهر الحالات هنا فور وصول أول طلب.</p>
                </div>
            @endif

            <div class="mt-5 flex items-center justify-between rounded-2xl bg-canvas/70 px-4 py-3">
                <span class="text-[10px] text-muted">نسبة إتمام الطلبات المنتهية</span>
                <span class="text-xs font-bold text-ink">{{ $summary['completion_rate'] }}%</span>
            </div>
        </div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <div class="card overflow-hidden xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-3 p-5 pb-4 sm:p-6 sm:pb-4">
                <div>
                    <h2 class="text-sm font-bold text-ink">الأكثر مبيعاً</h2>
                    <p class="mt-1 text-[11px] text-muted">أفضل المنتجات من الطلبات المسلّمة في الفترة</p>
                </div>
                <div class="flex rounded-full border border-line bg-canvas/60 p-0.5">
                    <a href="{{ route('analytics.index', ['period' => $period, 'sort' => 'revenue']) }}"
                       class="rounded-full px-3 py-1.5 text-[10px] font-bold transition {{ $productSort === 'revenue' ? 'bg-surface text-ink shadow-sm' : 'text-muted hover:text-ink' }}">
                        بالإيراد
                    </a>
                    <a href="{{ route('analytics.index', ['period' => $period, 'sort' => 'sold']) }}"
                       class="rounded-full px-3 py-1.5 text-[10px] font-bold transition {{ $productSort === 'sold' ? 'bg-surface text-ink shadow-sm' : 'text-muted hover:text-ink' }}">
                        بالكمية
                    </a>
                </div>
            </div>

            @if (count($topProducts) > 0)
                <div class="table-wrap">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>المنتج</th>
                                <th>الكمية</th>
                                <th>الإيراد</th>
                                <th class="w-[28%]">المقارنة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topProducts as $index => $product)
                                @php $share = max(6, round((float) $product[$productMetric] / $maxProductMetric * 100)); @endphp
                                <tr>
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-canvas text-[10px] font-bold text-muted">{{ $index + 1 }}</span>
                                            <p class="min-w-0 truncate text-[11px] font-bold text-ink">{{ $product['product_name'] }}</p>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap text-[11px] text-ink-soft">{{ $product['sold'] }}</td>
                                    <td class="whitespace-nowrap text-[11px] font-bold text-ink">{{ Money::format((int) $product['revenue']) }}</td>
                                    <td>
                                        <div class="h-1.5 overflow-hidden rounded-full bg-canvas">
                                            <div class="h-full rounded-full bg-gradient-to-l from-brand to-gold" style="width: {{ $share }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 pb-8 pt-2">
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-line px-6 py-10 text-center">
                        <p class="text-xs font-bold text-ink">لا توجد مبيعات مسلّمة في الفترة</p>
                        <p class="mt-1 text-[10px] text-muted">جرّبي فترة أطول لمشاهدة أداء منتجاتك.</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="card p-5 sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-sm font-bold text-ink">أيام الذروة</h2>
                    <p class="mt-1 text-[11px] text-muted">توزيع الطلبات على أيام الأسبوع</p>
                </div>
                <span class="flex size-9 items-center justify-center rounded-full bg-gold-soft text-[#a47b32]">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                </span>
            </div>

            <div class="mt-5 h-52 w-full">
                <canvas x-data x-init="window.Dukkani.barChart($refs.chart, @js($weekdayChart))" x-ref="chart" class="size-full" role="img" aria-label="توزيع الطلبات على أيام الأسبوع"></canvas>
            </div>

            <div class="mt-4 rounded-2xl bg-canvas/70 px-4 py-3">
                @if ($weekdayPeak)
                    <p class="text-[10px] text-muted">أكثر الأيام ازدحاماً</p>
                    <p class="mt-1 flex items-baseline gap-2">
                        <span class="text-xs font-bold text-ink">{{ $weekdayPeak['label'] }}</span>
                        <span class="text-[10px] text-muted">{{ $weekdayPeak['value'] }} طلبات</span>
                    </p>
                @else
                    <p class="text-[10px] text-muted">لا توجد طلبات مجدولة في هذه الفترة.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <div class="card overflow-hidden xl:col-span-2">
            <div class="flex items-start justify-between p-5 pb-4 sm:p-6 sm:pb-4">
                <div>
                    <h2 class="text-sm font-bold text-ink">أفضل العملاء</h2>
                    <p class="mt-1 text-[11px] text-muted">حسب إجمالي مشترياتهم المسلّمة في الفترة</p>
                </div>
                <a href="{{ route('customers.index') }}" class="rounded-full bg-canvas px-3 py-1 text-[10px] font-bold text-muted transition hover:bg-brand-soft hover:text-brand-dark">كل العملاء</a>
            </div>

            @if (count($topCustomers) > 0)
                <div class="table-wrap">
                    <table class="table-base">
                        <thead>
                            <tr>
                                <th>العميل</th>
                                <th>الطلبات</th>
                                <th>المشتريات</th>
                                <th class="w-[28%]">المقارنة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($topCustomers as $customer)
                                @php $share = max(6, round((float) $customer['spent'] / $maxCustomerSpent * 100)); @endphp
                                <tr class="cursor-pointer" onclick="window.location='{{ route('customers.show', $customer['id']) }}'">
                                    <td>
                                        <div class="flex items-center gap-2.5">
                                            <x-avatar :name="$customer['name']" size="sm" tone="stone" />
                                            <p class="min-w-0 truncate text-[11px] font-bold text-ink">{{ $customer['name'] }}</p>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap text-[11px] text-ink-soft">{{ $customer['orders_count'] }}</td>
                                    <td class="whitespace-nowrap text-[11px] font-bold text-ink">{{ Money::format((int) $customer['spent']) }}</td>
                                    <td>
                                        <div class="h-1.5 overflow-hidden rounded-full bg-canvas">
                                            <div class="h-full rounded-full bg-gradient-to-l from-sage to-gold" style="width: {{ $share }}%"></div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="px-6 pb-8 pt-2">
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-line px-6 py-10 text-center">
                        <p class="text-xs font-bold text-ink">لا توجد مشتريات مسلّمة في الفترة</p>
                        <p class="mt-1 text-[10px] text-muted">سيظهر أفضل عملائك هنا بعد أول طلب مسلّم.</p>
                    </div>
                </div>
            @endif
        </div>

        <div class="card p-5 sm:p-6">
            <div>
                <h2 class="text-sm font-bold text-ink">الاستلام والدفع</h2>
                <p class="mt-1 text-[11px] text-muted">كيف يفضّل عملاؤك استلام طلباتهم</p>
            </div>

            <div class="mt-5 flex items-center gap-4">
                <div class="relative size-24 shrink-0">
                    @if (array_sum($fulfillmentChart['values']) > 0)
                        <canvas x-data x-init="window.Dukkani.doughnutChart($refs.chart, @js($fulfillmentChart))" x-ref="chart" class="size-full" role="img" aria-label="توزيع طرق الاستلام"></canvas>
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-display text-lg font-bold leading-none text-ink">{{ array_sum($fulfillmentChart['values']) }}</span>
                            <span class="mt-0.5 text-[8px] text-muted">طلباً</span>
                        </div>
                    @else
                        <div class="flex size-full items-center justify-center rounded-full border border-dashed border-line text-[9px] text-muted">لا بيانات</div>
                    @endif
                </div>
                <div class="min-w-0 flex-1 space-y-2.5">
                    @forelse ($fulfillmentChart['labels'] as $index => $label)
                        <div class="flex items-center gap-2">
                            <span class="size-2 shrink-0 rounded-full" style="background-color: {{ $fulfillmentChart['colors'][$index] }}"></span>
                            <span class="min-w-0 flex-1 truncate text-[10px] text-muted">{{ $label }}</span>
                            <span class="text-[10px] font-bold text-ink">{{ $fulfillmentChart['values'][$index] }}</span>
                        </div>
                    @empty
                        <p class="text-[10px] leading-5 text-muted">لا توجد طلبات في الفترة.</p>
                    @endforelse
                </div>
            </div>

            <div class="my-5 divider"></div>

            <div>
                <h3 class="text-xs font-bold text-ink">طرق الدفع</h3>
                <p class="mt-1 text-[10px] text-muted">توزيع الطلبات غير الملغاة</p>
            </div>

            <div class="mt-4 flex items-center gap-4">
                <div class="relative size-24 shrink-0">
                    @if (array_sum($paymentChart['values']) > 0)
                        <canvas x-data x-init="window.Dukkani.doughnutChart($refs.chart, @js($paymentChart))" x-ref="chart" class="size-full" role="img" aria-label="توزيع طرق الدفع"></canvas>
                        <div class="pointer-events-none absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-display text-lg font-bold leading-none text-ink">{{ array_sum($paymentChart['values']) }}</span>
                            <span class="mt-0.5 text-[8px] text-muted">طلباً</span>
                        </div>
                    @else
                        <div class="flex size-full items-center justify-center rounded-full border border-dashed border-line text-[9px] text-muted">لا بيانات</div>
                    @endif
                </div>
                <div class="min-w-0 flex-1 space-y-2.5">
                    @forelse ($paymentChart['labels'] as $index => $label)
                        <div class="flex items-center gap-2">
                            <span class="size-2 shrink-0 rounded-full" style="background-color: {{ $paymentChart['colors'][$index] }}"></span>
                            <span class="min-w-0 flex-1 truncate text-[10px] text-muted">{{ $label }}</span>
                            <span class="text-[10px] font-bold text-ink">{{ $paymentChart['values'][$index] }}</span>
                        </div>
                    @empty
                        <p class="text-[10px] leading-5 text-muted">لا توجد طلبات في الفترة.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 flex items-start gap-2.5 rounded-2xl border border-line/70 bg-surface/60 px-4 py-3.5">
        <span class="mt-0.5 flex size-5 shrink-0 items-center justify-center rounded-full bg-brand-soft text-brand">
            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25 12 10.5m0 0 .75.75m-.75-.75v5.25m9-5.25a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
        </span>
        <p class="text-[10px] leading-5 text-muted">
            <span class="font-bold text-ink">كيف تُحسب الأرقام؟</span>
            الإيرادات تشمل الطلبات المسلّمة فقط، بينما تعتمد بقية العدادات على كل الطلبات غير الملغاة. تُحدّث البيانات مباشرة من متجرك.
        </p>
    </div>
@endsection
