@extends('layouts.dashboard')

@section('title', 'العملاء')
@section('page-title', 'العملاء')

@section('content')
    @php
        use App\Support\Money;

        $queryFor = function (array $overrides = []) {
            $query = array_merge(request()->query(), $overrides);

            return array_filter($query, fn ($value) => $value !== null && $value !== '');
        };

        $filtersActive = $filters['q'] !== '' || $filters['tag'] !== '';
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-display text-[2rem] font-bold leading-tight text-ink sm:text-[2.35rem]">العملاء</h1>
            <p class="mt-2 max-w-xl text-sm leading-6 text-muted">
                دفتر عملائك: تواصلي معهم، شاهدي تاريخهم، وأضيفي وسوماً تساعدك على معرفة الأوفياء.
            </p>
        </div>

        <a href="{{ route('orders.index') }}" class="btn-secondary btn-sm">
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 3h8a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2V5a2 2 0 0 1 2-2Z"/></svg>
            فتح الطلبات
        </a>
    </div>

    <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <div class="card flex items-center gap-3 p-4">
            <span class="kpi-icon size-10 bg-brand-soft text-brand"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>
            <div>
                <p class="text-[11px] font-bold text-muted">إجمالي العملاء</p>
                <p class="font-display text-xl font-bold text-ink">{{ $stats['total'] }}</p>
            </div>
        </div>
        <div class="card flex items-center gap-3 p-4">
            <span class="kpi-icon size-10 bg-sage-soft text-sage"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992V4.356M2.985 19.644v-4.992h4.992m-4.587-2.29a8.25 8.25 0 0 1 13.803-3.7l3.182 3.182M2.625 14.25l3.182 3.182a8.25 8.25 0 0 0 13.803-3.7"/></svg></span>
            <div>
                <p class="text-[11px] font-bold text-muted">عملاء متكررون</p>
                <p class="font-display text-xl font-bold text-ink">{{ $stats['repeat'] }}</p>
            </div>
        </div>
        <div class="card flex items-center gap-3 p-4">
            <span class="kpi-icon size-10 bg-gold-soft text-[#a47b32]"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 0 0-.182.557l1.285 5.385a.562.562 0 0 1-.84.61l-4.725-2.885a.562.562 0 0 0-.586 0L6.982 20.54a.562.562 0 0 1-.84-.61l1.285-5.386a.562.562 0 0 0-.182-.557l-4.204-3.602a.562.562 0 0 1 .321-.988l5.518-.442a.563.563 0 0 0 .475-.345L11.48 3.5Z"/></svg></span>
            <div>
                <p class="text-[11px] font-bold text-muted">وسم VIP</p>
                <p class="font-display text-xl font-bold text-ink">{{ $stats['vip'] }}</p>
            </div>
        </div>
        <div class="card flex items-center gap-3 p-4">
            <span class="kpi-icon size-10 bg-canvas text-ink-soft"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg></span>
            <div>
                <p class="text-[11px] font-bold text-muted">إجمالي المشتريات</p>
                <p class="font-display text-xl font-bold text-ink">{{ Money::format($stats['spent']) }}</p>
            </div>
        </div>
    </div>

    {{-- Tag chips --}}
    @if ($allTags->isNotEmpty())
        <div class="mt-5 flex gap-2 overflow-x-auto pb-1 no-scrollbar">
            <a href="{{ route('customers.index', $queryFor(['tag' => null, 'page' => null])) }}"
               class="inline-flex shrink-0 items-center rounded-full border px-3.5 py-2 text-[11px] font-bold transition {{ $filters['tag'] === '' ? 'border-brand bg-brand text-white' : 'border-line bg-surface text-ink-soft hover:border-brand/30 hover:text-ink' }}">
                كل الوسوم
            </a>
            @foreach ($allTags as $tag)
                <a href="{{ route('customers.index', $queryFor(['tag' => $tag, 'page' => null])) }}"
                   class="inline-flex shrink-0 items-center gap-1.5 rounded-full border px-3.5 py-2 text-[11px] font-bold transition {{ $filters['tag'] === $tag ? 'border-brand bg-brand text-white' : 'border-line bg-surface text-ink-soft hover:border-brand/30 hover:text-ink' }}">
                    <span class="size-1.5 rounded-full {{ $filters['tag'] === $tag ? 'bg-white/70' : 'bg-gold' }}"></span>
                    {{ $tag }}
                </a>
            @endforeach
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('customers.index') }}" class="card mt-4 p-4">
        @if ($filters['tag'] !== '')
            <input type="hidden" name="tag" value="{{ $filters['tag'] }}">
        @endif
        <div class="flex flex-wrap items-end gap-3">
            <div class="min-w-[14rem] flex-1">
                <label class="label !mb-1.5 !text-[10px] !text-muted">بحث</label>
                <div class="relative">
                    <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="اسم العميل أو رقم الهاتف..." class="input !py-2.5 !ps-10">
                    <svg class="pointer-events-none absolute inset-y-0 start-3.5 my-auto size-4 text-muted/60" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                </div>
            </div>

            <div class="w-[11rem]">
                <label class="label !mb-1.5 !text-[10px] !text-muted">الترتيب</label>
                <select name="sort" onchange="this.form.submit()" class="select !py-2.5">
                    <option value="recent" @selected($filters['sort'] === 'recent')>آخر طلب</option>
                    <option value="spent" @selected($filters['sort'] === 'spent')>الأعلى إنفاقاً</option>
                    <option value="orders" @selected($filters['sort'] === 'orders')>الأكثر طلباً</option>
                    <option value="name" @selected($filters['sort'] === 'name')>الاسم</option>
                </select>
            </div>

            <button class="btn-secondary btn-sm">بحث</button>

            @if ($filtersActive)
                <a href="{{ route('customers.index') }}" class="btn-ghost btn-sm text-muted">مسح الفلاتر</a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="card mt-4 overflow-hidden">
        @if ($customers->isEmpty())
            <div class="px-6 py-16 text-center">
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-canvas text-muted">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z"/></svg>
                </span>
                <p class="mt-4 text-sm font-bold text-ink">{{ $filtersActive ? 'لا يوجد عملاء مطابقون' : 'لا يوجد عملاء بعد' }}</p>
                <p class="mt-1 text-[11px] text-muted">{{ $filtersActive ? 'جرّبي تغيير البحث أو الوسم.' : 'سيظهر العملاء هنا تلقائياً عند أول طلب.' }}</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="table-base">
                    <thead>
                        <tr>
                            <th>العميل</th>
                            <th>الطلبات</th>
                            <th>إجمالي الشراء</th>
                            <th>آخر طلب</th>
                            <th>الوسوم</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                            @php
                                $last = $customer->last_order_at ? \Illuminate\Support\Carbon::parse($customer->last_order_at) : null;
                                $spent = (int) ($customer->spent_fils ?? 0);
                            @endphp
                            <tr class="cursor-pointer" onclick="window.location='{{ route('customers.show', $customer) }}'">
                                <td>
                                    <div class="flex items-center gap-2.5">
                                        <x-avatar :name="$customer->name" size="sm" />
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <p class="truncate text-xs font-bold text-ink">{{ $customer->name }}</p>
                                                @if ($customer->orders_count > 1)
                                                    <span class="rounded-full bg-brand-soft px-1.5 py-0.5 text-[8px] font-bold text-brand-dark">متكرر</span>
                                                @endif
                                            </div>
                                            <p class="mt-0.5 text-[9px] text-muted" dir="ltr">{{ $customer->phone }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-xs font-bold text-ink-soft">{{ $customer->orders_count }}</span>
                                </td>
                                <td>
                                    <p class="text-xs font-bold text-ink">{{ Money::format($spent) }}</p>
                                </td>
                                <td class="whitespace-nowrap">
                                    @if ($last)
                                        <p class="text-[11px] text-ink-soft">{{ $last->translatedFormat('j M Y') }}</p>
                                        <p class="mt-0.5 text-[9px] text-muted/70">{{ $last->diffForHumans() }}</p>
                                    @else
                                        <span class="text-[10px] text-muted/60">—</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="flex max-w-[10rem] flex-wrap gap-1">
                                        @forelse (array_slice($customer->tags ?? [], 0, 2) as $tag)
                                            <span class="rounded-full border border-line bg-surface px-2 py-0.5 text-[9px] font-bold text-muted">{{ $tag }}</span>
                                        @empty
                                            <span class="text-[9px] text-muted/50">—</span>
                                        @endforelse
                                        @if (count($customer->tags ?? []) > 2)
                                            <span class="rounded-full bg-canvas px-2 py-0.5 text-[9px] font-bold text-muted">+{{ count($customer->tags) - 2 }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="flex justify-end">
                                        <a href="{{ route('customers.show', $customer) }}" class="icon-btn !size-8" aria-label="فتح العميل" onclick="event.stopPropagation()">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <x-pagination :paginator="$customers" label="عميل" />
@endsection