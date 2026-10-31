@extends('layouts.dashboard')

@section('title', 'الطلبات')
@section('page-title', 'الطلبات')

@section('content')
    @php
        use App\Enums\OrderStatus;
        use App\Support\Money;

        $queryFor = function (array $overrides = []) {
            $query = array_merge(request()->query(), $overrides);

            return array_filter($query, fn ($value) => $value !== null && $value !== '');
        };

        $tabs = collect([
            ['key' => 'all', 'label' => 'الكل'],
            ['key' => 'active', 'label' => 'نشطة'],
            ...collect(OrderStatus::cases())->map(fn ($status) => ['key' => $status->value, 'label' => $status->label()])->all(),
        ]);

        $boardColumns = [
            OrderStatus::New,
            OrderStatus::Confirmed,
            OrderStatus::InProduction,
            OrderStatus::Ready,
            OrderStatus::OutForDelivery,
            OrderStatus::Delivered,
            OrderStatus::Cancelled,
        ];

        $grouped = $view === 'board'
            ? collect($orders)->groupBy(fn ($order) => $order->status->value)
            : collect();
        $filtersActive = $filters['status'] !== 'all'
            || $filters['date'] !== 'all'
            || $filters['fulfillment'] !== 'all'
            || $filters['payment'] !== 'all'
            || $filters['q'] !== '';
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-display text-[2rem] font-bold leading-tight text-ink sm:text-[2.35rem]">الطلبات</h1>
            <p class="mt-2 max-w-xl text-sm leading-6 text-muted">
                تابعي كل طلب من لحظة وصوله حتى التسليم، وحرّكي حالته بضغطة واحدة.
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <div class="inline-flex rounded-full border border-line bg-surface p-1">
                <a href="{{ route('orders.index', $queryFor(['view' => 'list', 'page' => null])) }}"
                   class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-[11px] font-bold transition {{ $view === 'list' ? 'bg-brand text-white shadow-[0_8px_18px_-12px_rgba(142,68,37,0.9)]' : 'text-muted hover:text-ink' }}">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    قائمة
                </a>
                <a href="{{ route('orders.index', $queryFor(['view' => 'board', 'page' => null])) }}"
                   class="inline-flex items-center gap-1.5 rounded-full px-3.5 py-1.5 text-[11px] font-bold transition {{ $view === 'board' ? 'bg-brand text-white shadow-[0_8px_18px_-12px_rgba(142,68,37,0.9)]' : 'text-muted hover:text-ink' }}">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4h4v16H4zM10 4h4v10h-4zM16 4h4v7h-4z"/></svg>
                    لوحة
                </a>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card flex items-center gap-3 p-4">
            <span class="kpi-icon size-10 bg-brand-soft text-brand"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3h8a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2V5a2 2 0 0 1 2-2Z"/></svg></span>
            <div>
                <p class="text-[11px] font-bold text-muted">طلبات نشطة</p>
                <p class="font-display text-xl font-bold text-ink">{{ $stats['active'] }}</p>
            </div>
        </div>
        <div class="card flex items-center gap-3 p-4">
            <span class="kpi-icon size-10 bg-sage-soft text-sage"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
            <div>
                <p class="text-[11px] font-bold text-muted">مجدولة اليوم</p>
                <p class="font-display text-xl font-bold text-ink">{{ $stats['today'] }}</p>
            </div>
        </div>
        <div class="card flex items-center gap-3 p-4">
            <span class="kpi-icon size-10 bg-danger-soft text-danger"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg></span>
            <div>
                <p class="text-[11px] font-bold text-muted">متأخرة</p>
                <p class="font-display text-xl font-bold text-ink">{{ $stats['late'] }}</p>
            </div>
        </div>
        <div class="card flex items-center gap-3 p-4">
            <span class="kpi-icon size-10 bg-gold-soft text-[#a47b32]"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg></span>
            <div>
                <p class="text-[11px] font-bold text-muted">عربونات معلّقة</p>
                <p class="font-display text-xl font-bold text-ink">{{ Money::format($stats['deposits']) }}</p>
            </div>
        </div>
    </div>

    @if ($view === 'list')
        <div class="mt-5 flex gap-2 overflow-x-auto pb-1 no-scrollbar">
            @foreach ($tabs as $tab)
                @php
                    $active = $filters['status'] === $tab['key'];
                    $count = $statusCounts[$tab['key']] ?? 0;
                @endphp
                <a href="{{ route('orders.index', $queryFor(['status' => $tab['key'], 'page' => null])) }}"
                   class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3.5 py-2 text-[11px] font-bold transition {{ $active ? 'border-brand bg-brand text-white' : 'border-line bg-surface text-ink-soft hover:border-brand/30 hover:text-ink' }}">
                    {{ $tab['label'] }}
                    <span class="rounded-full px-1.5 py-0.5 text-[9px] {{ $active ? 'bg-white/25 text-white' : 'bg-canvas text-muted' }}">{{ $count }}</span>
                </a>
            @endforeach
        </div>
    @endif

    <form method="GET" action="{{ route('orders.index') }}" class="card mt-4 p-4">
        <input type="hidden" name="view" value="{{ $view }}">
        <input type="hidden" name="status" value="{{ $filters['status'] }}">

        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[14rem] flex-1">
                <label class="label !mb-1.5 !text-[10px] !text-muted">بحث</label>
                <div class="relative">
                    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="رقم الطلب، اسم العميل، أو الهاتف…" class="input !py-2.5 !ps-10">
                    <svg class="pointer-events-none absolute inset-y-0 start-3.5 my-auto size-4 text-muted/60" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>
            </div>

            <div class="w-[9.5rem]">
                <label class="label !mb-1.5 !text-[10px] !text-muted">الموعد</label>
                <select name="date" onchange="this.form.submit()" class="select !py-2.5">
                    <option value="all" @selected($filters['date'] === 'all')>كل المواعيد</option>
                    <option value="today" @selected($filters['date'] === 'today')>اليوم</option>
                    <option value="upcoming" @selected($filters['date'] === 'upcoming')>القادمة</option>
                    <option value="late" @selected($filters['date'] === 'late')>المتأخرة</option>
                    <option value="week" @selected($filters['date'] === 'week')>هذا الأسبوع</option>
                </select>
            </div>

            <div class="w-[9.5rem]">
                <label class="label !mb-1.5 !text-[10px] !text-muted">طريقة الاستلام</label>
                <select name="fulfillment" onchange="this.form.submit()" class="select !py-2.5">
                    <option value="all" @selected($filters['fulfillment'] === 'all')>الكل</option>
                    <option value="delivery" @selected($filters['fulfillment'] === 'delivery')>توصيل</option>
                    <option value="pickup" @selected($filters['fulfillment'] === 'pickup')>استلام</option>
                </select>
            </div>

            <div class="w-[9.5rem]">
                <label class="label !mb-1.5 !text-[10px] !text-muted">الدفع</label>
                <select name="payment" onchange="this.form.submit()" class="select !py-2.5">
                    <option value="all" @selected($filters['payment'] === 'all')>الكل</option>
                    <option value="cash" @selected($filters['payment'] === 'cash')>نقداً</option>
                    <option value="cliq" @selected($filters['payment'] === 'cliq')>CliQ</option>
                    <option value="bank_transfer" @selected($filters['payment'] === 'bank_transfer')>حوالة بنكية</option>
                </select>
            </div>

            <div class="w-[10.5rem]">
                <label class="label !mb-1.5 !text-[10px] !text-muted">الترتيب</label>
                <select name="sort" onchange="this.form.submit()" class="select !py-2.5">
                    <option value="schedule" @selected($filters['sort'] === 'schedule')>الأقرب موعداً</option>
                    <option value="recent" @selected($filters['sort'] === 'recent')>الأحدث</option>
                    <option value="total" @selected($filters['sort'] === 'total')>الأعلى قيمة</option>
                </select>
            </div>

            <div class="flex items-center gap-2">
                <button class="btn-primary btn-sm">تصفية</button>
                @if ($filtersActive)
                    <a href="{{ route('orders.index', ['view' => $view]) }}" class="btn-ghost btn-sm text-muted">مسح</a>
                @endif
            </div>
        </div>
    </form>

    @if ($view === 'board')
        <div class="mt-4 flex gap-4 overflow-x-auto pb-3">
            @foreach ($boardColumns as $column)
                @php $columnOrders = $grouped->get($column->value, collect()); @endphp
                <div class="flex w-[17rem] shrink-0 flex-col">
                    <div class="flex items-center justify-between rounded-2xl border border-line/70 bg-surface px-3.5 py-2.5">
                        <div class="flex items-center gap-2">
                            <x-status-badge :status="$column" />
                        </div>
                        <span class="text-[11px] font-bold text-muted">{{ $columnOrders->count() }}</span>
                    </div>

                    <div class="mt-3 flex-1 space-y-2.5">
                        @forelse ($columnOrders as $order)
                            <x-order-card :order="$order" />
                        @empty
                            <div class="rounded-2xl border border-dashed border-line px-4 py-8 text-center text-[10px] text-muted/70">
                                لا توجد طلبات هنا.
                            </div>
                        @endforelse

                        @if ($columnOrders->count() >= 240)
                            <p class="text-center text-[9px] text-muted/60">يتم عرض أول ٢٤٠ طلباً فقط.</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card mt-4 overflow-hidden">
            <div class="table-wrap">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>الطلب</th>
                            <th>العميل</th>
                            <th>التفاصيل</th>
                            <th>الموعد</th>
                            <th>الدفع</th>
                            <th>الإجمالي</th>
                            <th>الحالة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr>
                                <td>
                                    <a href="{{ route('orders.show', $order) }}" class="font-mono text-[11px] font-bold text-ink transition hover:text-brand" dir="ltr">{{ $order->number }}</a>
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
                                <td class="max-w-[16rem]">
                                    <p class="truncate text-[11px] text-ink-soft">{{ $order->itemsSummary() }}</p>
                                    <p class="mt-0.5 text-[9px] text-muted/70">{{ $order->itemsCount() }} قطعة • {{ $order->fulfillment_type->label() }}</p>
                                </td>
                                <td class="whitespace-nowrap">
                                    <p class="text-[11px] text-ink-soft">{{ $order->scheduled_at->translatedFormat('j M Y') }}</p>
                                    <p class="mt-0.5 text-[9px] {{ $order->isLate() ? 'font-bold text-danger' : 'text-muted/70' }}">{{ $order->scheduled_at->translatedFormat('g:i A') }}{{ $order->isLate() ? ' • متأخر' : '' }}</p>
                                </td>
                                <td class="whitespace-nowrap">
                                    <p class="text-[11px] text-ink-soft">{{ $order->payment_method?->label() ?? '—' }}</p>
                                    @if ($order->deposit_fils > 0)
                                        <p class="mt-0.5 text-[9px] {{ $order->deposit_status === \App\Enums\DepositStatus::Pending ? 'font-bold text-[#a47b32]' : 'text-muted/70' }}">{{ $order->deposit_status->label() }}</p>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap">
                                    <p class="text-[11px] font-bold text-ink">{{ $order->totalFormatted() }}</p>
                                    @if ($order->deposit_fils > 0)
                                        <p class="mt-0.5 text-[9px] text-muted/70">متبقٍ {{ $order->balanceFormatted() }}</p>
                                    @endif
                                </td>
                                <td><x-status-badge :status="$order->status" /></td>
                                <td>
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if ($order->status === OrderStatus::New)
                                            <form method="POST" action="{{ route('orders.status', $order) }}">
                                                @csrf
                                                @method('PATCH')
                                                <input type="hidden" name="status" value="{{ OrderStatus::Confirmed->value }}">
                                                <button class="rounded-full bg-brand-soft px-3 py-1.5 text-[10px] font-bold text-brand-dark transition hover:bg-brand hover:text-white">تأكيد</button>
                                            </form>
                                        @endif
                                        <a href="{{ route('orders.show', $order) }}" class="icon-btn !size-8" aria-label="فتح الطلب">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-14 text-center">
                                    <span class="mx-auto flex size-11 items-center justify-center rounded-full bg-canvas text-muted">
                                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2Z"/></svg>
                                    </span>
                                    <p class="mt-3 text-xs font-bold text-ink">لا توجد طلبات مطابقة</p>
                                    <p class="mt-1 text-[10px] text-muted">جرّبي تغيير الفلاتر أو مسحها.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($orders->hasPages())
            <div class="mt-5 flex flex-col items-center justify-between gap-3 sm:flex-row">
                <p class="text-[10px] text-muted">عرض {{ $orders->firstItem() }}–{{ $orders->lastItem() }} من {{ $orders->total() }} طلب</p>
                <div class="flex items-center gap-2">
                    @if ($orders->onFirstPage())
                        <span class="btn-secondary btn-sm cursor-not-allowed opacity-50">السابق</span>
                    @else
                        <a href="{{ $orders->previousPageUrl() }}" class="btn-secondary btn-sm">السابق</a>
                    @endif
                    <span class="text-[11px] font-bold text-ink-soft">{{ $orders->currentPage() }} / {{ $orders->lastPage() }}</span>
                    @if ($orders->hasMorePages())
                        <a href="{{ $orders->nextPageUrl() }}" class="btn-secondary btn-sm">التالي</a>
                    @else
                        <span class="btn-secondary btn-sm cursor-not-allowed opacity-50">التالي</span>
                    @endif
                </div>
            </div>
        @else
            <p class="mt-5 text-[10px] text-muted">{{ $orders->total() }} طلب</p>
        @endif
    @endif
@endsection
