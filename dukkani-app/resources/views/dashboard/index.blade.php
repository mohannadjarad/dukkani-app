@extends('layouts.dashboard')

@section('title', 'نظرة عامة')
@section('page-title', 'نظرة عامة')

@section('content')
    @php
        $hour = (int) now()->format('G');
        $greeting = $hour < 12 ? 'صباح الخير' : 'مساء الخير';
        $firstName = explode(' ', trim(auth()->user()->name))[0];
        $maxSold = max(1, collect($topProducts)->max('sold') ?? 1);
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[11px] font-medium text-muted">{{ now()->translatedFormat('l، j F Y') }}</p>
            <h1 class="mt-1.5 font-display text-[2rem] font-bold leading-tight text-ink sm:text-[2.35rem]">
                {{ $greeting }}، {{ $firstName }} <span class="text-brand">🌾</span>
            </h1>
            <p class="mt-2 max-w-xl text-sm leading-6 text-muted">
                هذا ملخص متجرك اليوم. عندك <span class="font-bold text-ink">{{ $kpis['pending'] }}</span> طلباً بانتظار المتابعة،
                و<span class="font-bold text-ink">{{ $todayOrders->count() }}</span> مواعيد مجدولة اليوم.
            </p>
        </div>

        <div class="flex items-center gap-2">
            <a href="{{ route('dashboard') }}" class="btn-secondary btn-sm">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992V4.356M2.985 19.644v-4.992h4.992m-4.587-2.29a8.25 8.25 0 0 1 13.803-3.7l3.182 3.182M2.625 14.25l3.182 3.182a8.25 8.25 0 0 0 13.803-3.7"/></svg>
                تحديث
            </a>
            <span class="hidden items-center gap-2 rounded-full border border-line bg-surface px-4 py-2 text-[11px] font-bold text-muted sm:inline-flex">
                <span class="size-1.5 rounded-full {{ $store->isOpenNow() ? 'bg-emerald-500' : 'bg-stone-400' }}"></span>
                {{ $store->isOpenNow() ? 'المتجر مفتوح الآن' : 'المتجر مغلق الآن' }}
            </span>
        </div>
    </div>

    <div class="mt-7 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card card-hover p-5">
            <div class="flex items-start justify-between">
                <span class="kpi-icon bg-brand-soft text-brand">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 0 0-2.25-2.25H15a3 3 0 1 1-6 0H5.25A2.25 2.25 0 0 0 3 12m18 0v6a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 9m18 0V6a2.25 2.25 0 0 0-2.25-2.25H5.25A2.25 2.25 0 0 0 3 6v3"/></svg>
                </span>
                @include('dashboard.partials.change', ['value' => $kpis['revenue_change'], 'positive' => $kpis['revenue_change'] >= 0])
            </div>
            <p class="mt-5 text-[11px] font-bold text-muted">إيرادات الشهر</p>
            <p class="mt-1 font-display text-[1.7rem] font-bold tracking-tight text-ink">{{ \App\Support\Money::format($kpis['revenue']) }}</p>
            <p class="mt-1 text-[10px] text-muted/70">من الطلبات المسلّمة هذا الشهر</p>
        </div>

        <div class="card card-hover p-5">
            <div class="flex items-start justify-between">
                <span class="kpi-icon bg-sage-soft text-sage">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                </span>
                @include('dashboard.partials.change', ['value' => $kpis['orders_change'], 'positive' => $kpis['orders_change'] >= 0])
            </div>
            <p class="mt-5 text-[11px] font-bold text-muted">طلبات الشهر</p>
            <p class="mt-1 font-display text-[1.7rem] font-bold tracking-tight text-ink">{{ $kpis['orders'] }} <span class="font-sans text-xs font-medium text-muted">طلباً</span></p>
            <p class="mt-1 text-[10px] text-muted/70">متوسط الطلب {{ \App\Support\Money::format($kpis['avg_order']) }}</p>
        </div>

        <a href="{{ route('orders.index', ['status' => 'active']) }}" class="card card-hover block p-5">
            <div class="flex items-start justify-between">
                <span class="kpi-icon bg-gold-soft text-[#a47b32]">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                </span>
                <span class="rounded-full bg-gold-soft px-2.5 py-1 text-[10px] font-bold text-[#8a672c]">تحتاج متابعة</span>
            </div>
            <p class="mt-5 text-[11px] font-bold text-muted">قيد التنفيذ</p>
            <p class="mt-1 font-display text-[1.7rem] font-bold tracking-tight text-ink">{{ $kpis['pending'] }} <span class="font-sans text-xs font-medium text-muted">طلباً</span></p>
            <p class="mt-1 text-[10px] text-muted/70">جديد، مؤكد، أو قيد التحضير</p>
        </a>

        <div class="card card-hover p-5">
            <div class="flex items-start justify-between">
                <span class="kpi-icon bg-violet-50 text-violet-600">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/></svg>
                </span>
                <span class="rounded-full bg-violet-50 px-2.5 py-1 text-[10px] font-bold text-violet-600">بانتظار التحويل</span>
            </div>
            <p class="mt-5 text-[11px] font-bold text-muted">عربونات معلّقة</p>
            <p class="mt-1 font-display text-[1.7rem] font-bold tracking-tight text-ink">{{ \App\Support\Money::format($kpis['outstanding_deposits']) }}</p>
            <p class="mt-1 text-[10px] text-muted/70">من طلبات CliQ والتحويل البنكي</p>
        </div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-3">
        <div class="card p-5 sm:p-6 xl:col-span-2">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-ink">حركة الإيرادات</h3>
                    <p class="mt-1 text-[11px] text-muted">الطلبات المسلّمة خلال آخر 14 يوماً</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="flex items-center gap-1.5 text-[10px] font-medium text-muted">
                        <span class="size-2 rounded-full bg-brand"></span>
                        الإيرادات
                    </span>
                    <span class="rounded-full border border-line px-3 py-1 text-[10px] font-bold text-muted">آخر 14 يوماً</span>
                </div>
            </div>

            <div class="mt-6 h-[17rem] w-full">
                <canvas x-data x-init="window.Dukkani.revenueChart($refs.chart, @js($chart))" x-ref="chart" class="size-full"></canvas>
            </div>

            <div class="mt-4 flex items-center justify-between rounded-2xl bg-canvas/70 px-4 py-3">
                <p class="text-[10px] text-muted">إجمالي الفترة</p>
                <p class="text-xs font-bold text-ink">{{ \App\Support\Money::format((int) (collect($chart['values'])->sum() * 1000)) }}</p>
            </div>
        </div>

        <div class="card flex flex-col p-5 sm:p-6">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-sm font-bold text-ink">الأكثر مبيعاً</h3>
                    <p class="mt-1 text-[11px] text-muted">حسب الكمية المطلوبة</p>
                </div>
                <span class="flex size-9 items-center justify-center rounded-full bg-gold-soft text-[#a47b32]">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.492a46.32 46.32 0 0 1 2.916.52 6.003 6.003 0 0 1-5.395 4.972m0 0a6.726 6.726 0 0 1-2.749 1.35m0 0a6.772 6.772 0 0 1-3.044 0"/></svg>
                </span>
            </div>

            <div class="mt-6 flex-1 space-y-5">
                @forelse ($topProducts as $index => $product)
                    <div>
                        <div class="flex items-center gap-3">
                            <span class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-canvas text-[10px] font-bold text-muted">{{ $index + 1 }}</span>
                            <p class="min-w-0 flex-1 truncate text-xs font-bold text-ink">{{ $product['product_name'] }}</p>
                            <span class="shrink-0 text-[10px] font-bold text-muted">{{ $product['sold'] }}</span>
                        </div>
                        <div class="mt-2 ms-10 h-1.5 overflow-hidden rounded-full bg-canvas">
                            <div class="h-full rounded-full bg-gradient-to-l from-brand to-gold" style="width: {{ max(8, round($product['sold'] / $maxSold * 100)) }}%"></div>
                        </div>
                    </div>
                @empty
                    <div class="flex h-full flex-col items-center justify-center text-center">
                        <p class="text-xs text-muted">لا توجد مبيعات مسلّمة بعد.</p>
                    </div>
                @endforelse
            </div>

            <div class="mt-6 rounded-2xl border border-line/70 bg-canvas/60 px-4 py-3">
                <p class="text-[10px] leading-5 text-muted">
                    <span class="font-bold text-ink">تحليلات أعمق:</span>
                    <a href="{{ route('analytics.index') }}" class="font-bold text-brand transition hover:text-brand-dark">افتحي صفحة التحليلات</a>
                    لمشاهدة أفضل المنتجات والعملاء وأيام الذروة.
                </p>
            </div>
        </div>
    </div>

    <div class="mt-4 grid gap-4 xl:grid-cols-5">
        <div class="card p-5 sm:p-6 xl:col-span-2">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="text-sm font-bold text-ink">جدول اليوم</h3>
                    <p class="mt-1 text-[11px] text-muted">{{ $todayOrders->count() }} مواعيد للاستلام أو التوصيل</p>
                </div>
                <span class="rounded-full bg-brand-soft px-3 py-1 text-[10px] font-bold text-brand-dark">{{ now()->translatedFormat('j F') }}</span>
            </div>

            <div class="mt-5 space-y-2.5">
                @forelse ($todayOrders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="group flex items-center gap-3 rounded-2xl border border-line/60 bg-white/40 p-3 transition hover:border-brand/20 hover:bg-white">
                        <div class="flex w-14 shrink-0 flex-col items-center rounded-xl bg-canvas py-2">
                            <span class="text-[11px] font-bold text-ink">{{ $order->scheduled_at->translatedFormat('g:i') }}</span>
                            <span class="text-[9px] text-muted">{{ $order->scheduled_at->translatedFormat('A') }}</span>
                        </div>
                        <x-avatar :name="$order->customer->name" size="sm" />
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <p class="truncate text-xs font-bold text-ink">{{ $order->customer->name }}</p>
                                <span class="shrink-0 text-[9px] text-muted/60" dir="ltr">{{ $order->number }}</span>
                            </div>
                            <p class="mt-0.5 truncate text-[10px] text-muted">
                                {{ $order->itemsSummary() }}
                            </p>
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1.5">
                            <x-status-badge :status="$order->status" />
                            <span class="text-[9px] text-muted/60">{{ $order->fulfillment_type->label() }}</span>
                        </div>
                    </a>
                @empty
                    <div class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-line px-6 py-10 text-center">
                        <span class="flex size-11 items-center justify-center rounded-full bg-canvas text-muted">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                        </span>
                        <p class="mt-3 text-xs font-bold text-ink">لا توجد طلبات مجدولة اليوم</p>
                        <p class="mt-1 text-[10px] text-muted">استفيدي من الوقت لمراجعة الطلبات القادمة.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <div class="card overflow-hidden xl:col-span-3">
            <div class="flex items-start justify-between p-5 pb-4 sm:p-6 sm:pb-4">
                <div>
                    <h3 class="text-sm font-bold text-ink">أحدث الطلبات</h3>
                    <p class="mt-1 text-[11px] text-muted">آخر ما وصل إلى المتجر</p>
                </div>
                <a href="{{ route('orders.index') }}" class="rounded-full bg-canvas px-3 py-1 text-[10px] font-bold text-muted transition hover:bg-brand-soft hover:text-brand-dark">عرض كل الطلبات</a>
            </div>

            <div class="table-wrap">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>الطلب</th>
                            <th>العميل</th>
                            <th>الموعد</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            <tr class="cursor-pointer" onclick="window.location='{{ route('orders.show', $order) }}'">
                                <td>
                                    <span class="font-mono text-[11px] font-bold text-brand" dir="ltr">{{ $order->number }}</span>
                                    <p class="mt-0.5 text-[9px] text-muted/60">{{ $order->created_at->diffForHumans() }}</p>
                                </td>
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <x-avatar :name="$order->customer->name" size="sm" tone="stone" />
                                        <div class="min-w-0">
                                            <p class="truncate text-[11px] font-bold text-ink">{{ $order->customer->name }}</p>
                                            <p class="truncate text-[9px] text-muted/70" dir="ltr">{{ $order->customer->phone }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <p class="text-[11px] text-ink-soft">{{ $order->scheduled_at->translatedFormat('j F') }}</p>
                                    <p class="mt-0.5 text-[9px] text-muted/70">{{ $order->fulfillment_type->label() }}</p>
                                </td>
                                <td class="whitespace-nowrap text-[11px] font-bold text-ink">{{ $order->totalFormatted() }}</td>
                                <td><x-status-badge :status="$order->status" /></td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-10 text-center text-xs text-muted">لا توجد طلبات بعد.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
