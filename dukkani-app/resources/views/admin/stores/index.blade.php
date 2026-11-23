@extends('layouts.admin')

@section('title', 'المتاجر')
@section('page-title', 'المتاجر')

@section('content')
    @php
        $planTone = [
            'free' => 'bg-canvas text-muted',
            'starter' => 'bg-brand-soft text-brand-dark',
            'pro' => 'bg-violet-50 text-violet-600',
        ];
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-display text-[2rem] font-bold leading-tight text-ink">المتاجر</h1>
            <p class="mt-2 max-w-xl text-sm leading-6 text-muted">أدِر خطط المتاجر، امنح حداً إضافياً للطلبات، أو علّق متجراً مؤقتاً.</p>
            <a href="{{ route('admin.stores.create') }}" class="btn-primary btn-sm mt-4">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                متجر جديد
            </a>
        </div>

        <div class="grid grid-cols-2 gap-2 sm:grid-cols-4">
            <div class="rounded-2xl border border-line/70 bg-surface px-3.5 py-2.5 text-center">
                <p class="text-[10px] font-bold text-muted">المتاجر</p>
                <p class="mt-0.5 font-display text-lg font-bold text-ink">{{ $summary['stores'] }}</p>
            </div>
            <div class="rounded-2xl border border-line/70 bg-surface px-3.5 py-2.5 text-center">
                <p class="text-[10px] font-bold text-muted">تجربة</p>
                <p class="mt-0.5 font-display text-lg font-bold text-ink">{{ $summary['trial'] }}</p>
            </div>
            <div class="rounded-2xl border border-line/70 bg-surface px-3.5 py-2.5 text-center">
                <p class="text-[10px] font-bold text-muted">مدفوعة</p>
                <p class="mt-0.5 font-display text-lg font-bold text-ink">{{ $summary['paid'] }}</p>
            </div>
            <div class="rounded-2xl border border-line/70 bg-surface px-3.5 py-2.5 text-center">
                <p class="text-[10px] font-bold text-muted">معلّقة</p>
                <p class="mt-0.5 font-display text-lg font-bold text-ink">{{ $summary['suspended'] }}</p>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="mt-5 flex items-start gap-3 rounded-2xl border border-danger/20 bg-danger-soft px-4 py-3.5 text-sm text-danger">
            <svg class="mt-0.5 size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 2.35 17.6A1.5 1.5 0 0 0 3.65 20h16.7a1.5 1.5 0 0 0 1.3-2.4L13.7 3.86a1.5 1.5 0 0 0-2.6 0Z"/></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <div class="mt-6 space-y-4">
        @forelse ($rows as $row)
            @php $store = $row['store']; @endphp
            <div class="card p-5 sm:p-6">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div class="flex min-w-0 items-start gap-3">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-2xl bg-brand-soft font-display text-lg font-bold text-brand-dark">{{ mb_substr($store->name, 0, 1) }}</span>
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <p class="text-sm font-bold text-ink">{{ $store->name }}</p>
                                <span class="rounded-full px-2.5 py-0.5 text-[10px] font-bold {{ $planTone[$row['plan']] ?? 'bg-canvas text-muted' }}">{{ $row['label'] }}</span>
                                @if ($row['trial'])
                                    <span class="rounded-full bg-gold-soft px-2.5 py-0.5 text-[10px] font-bold text-[#8a672c]">تجربة</span>
                                @endif
                                @if ($row['expired'])
                                    <span class="rounded-full bg-danger-soft px-2.5 py-0.5 text-[10px] font-bold text-danger">منتهية</span>
                                @endif
                                @unless ($row['active'])
                                    <span class="rounded-full bg-danger-soft px-2.5 py-0.5 text-[10px] font-bold text-danger">معلّق</span>
                                @endunless
                            </div>
                            <p class="mt-1 truncate text-[11px] text-muted">
                                {{ $row['owner']?->name ?? '—' }} • <span dir="ltr">{{ $row['owner']?->email ?? '—' }}</span>
                            </p>
                            <p class="mt-0.5 text-[11px] text-muted">
                                واتساب: <span dir="ltr">{{ $store->whatsapp ?: $store->phone ?: '—' }}</span>
                                @if ($store->city) • {{ $store->city }} @endif
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if ($row['active'])
                            <form method="POST" action="{{ route('admin.stores.suspend', $store) }}" onsubmit="return confirm('تعليق {{ $store->name }}؟ سيختفي المتجر عن العملاء.')">
                                @csrf
                                <button class="btn-secondary btn-sm !text-danger">تعليق</button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('admin.stores.activate', $store) }}">
                                @csrf
                                <button class="btn-secondary btn-sm">فتح المتجر</button>
                            </form>
                        @endif
                        <a href="{{ route('storefront.home', $store) }}" target="_blank" rel="noopener" class="btn-secondary btn-sm">عرض</a>
                    </div>
                </div>

                <dl class="mt-4 grid grid-cols-2 gap-3 border-t border-line/60 pt-4 text-[11px] sm:grid-cols-4">
                    <div>
                        <dt class="text-muted">طلبات الشهر</dt>
                        <dd class="mt-0.5 font-bold text-ink">
                            @if ($row['unlimited'])
                                {{ $row['orders'] }} <span class="font-sans text-[10px] font-medium text-muted">/ بلا حد</span>
                            @else
                                {{ $row['orders'] }} <span class="font-sans text-[10px] font-medium text-muted">/ {{ $row['limit'] }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-muted">حد إضافي</dt>
                        <dd class="mt-0.5 font-bold text-ink">{{ $row['override'] > 0 ? '+'.$row['override'] : '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">ينتهي في</dt>
                        <dd class="mt-0.5 font-bold text-ink">{{ $row['expires_at']?->translatedFormat('j F Y') ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-muted">آخر دخول</dt>
                        <dd class="mt-0.5 font-bold text-ink">{{ $row['owner']?->last_login_at?->translatedFormat('j F Y، g:i A') ?? '—' }}</dd>
                    </div>
                </dl>

                <div class="mt-4 space-y-4 border-t border-line/60 pt-4">
                    <form method="POST" action="{{ route('admin.stores.plan', $store) }}" x-data="{ plan: '{{ $row['stored_plan'] }}' }" class="flex flex-wrap items-end gap-2">
                        @csrf
                        <div>
                            <label class="label !mb-1.5 !text-[10px] !text-muted">الخطة</label>
                            <select name="plan" x-model="plan" class="input !py-2 !text-xs">
                                @foreach ($plans as $key => $plan)
                                    @php $sellable = ($plan['purchasable'] ?? true) !== false; @endphp
                                    @if ($sellable || $row['stored_plan'] === $key)
                                        <option value="{{ $key }}" @selected($row['stored_plan'] === $key) @disabled(! $sellable)>{{ $plan['label'] }}@unless ($sellable) — قريباً@endunless</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="label !mb-1.5 !text-[10px] !text-muted">تنتهي في</label>
                            <input type="date" name="ends_at" value="{{ $row['expires_at']?->format('Y-m-d') }}" min="{{ now()->toDateString() }}"
                                   x-bind:disabled="plan === 'free'" class="input !py-2 !text-xs" dir="ltr">
                        </div>
                        <label x-show="plan !== 'free'" class="flex h-[34px] items-center gap-1.5 rounded-xl border border-line bg-surface px-2.5 text-[10px] font-bold text-muted">
                            <input type="checkbox" name="is_trial" value="1" @checked($row['trial_flag']) class="size-3.5 rounded border-line text-brand">
                            فترة تجربة
                        </label>
                        <button class="btn-primary btn-sm">حفظ الخطة</button>
                        <p class="basis-full text-[10px] leading-4 text-muted/70">
                            اتركي التاريخ فارغاً لخطة بلا انتهاء. التجربة تُحسب حتى نهاية اليوم المحدد.
                        </p>
                    </form>

                    <div class="flex flex-wrap items-end gap-4">
                        @if ($row['stored_plan'] !== 'free' && $row['expires_at'] !== null)
                            <form method="POST" action="{{ route('admin.stores.extend', $store) }}" class="flex items-end gap-2">
                                @csrf
                                <div>
                                    <label class="label !mb-1.5 !text-[10px] !text-muted">تمديد</label>
                                    <select name="extend_months" class="input w-28 !py-2 !text-xs">
                                        <option value="1">شهر</option>
                                        <option value="3" selected>3 أشهر</option>
                                        <option value="6">6 أشهر</option>
                                        <option value="12">سنة</option>
                                    </select>
                                </div>
                                <button class="btn-secondary btn-sm">مدّد الخطة</button>
                            </form>
                        @else
                            <p class="pb-2 text-[10px] leading-4 text-muted/70">
                                {{ $row['stored_plan'] === 'free' ? 'فعّلي خطة بتاريخ انتهاء أولاً، ثم يمكنك تمديدها.' : 'هذه الخطة بلا تاريخ انتهاء.' }}
                            </p>
                        @endif

                        <form method="POST" action="{{ route('admin.stores.override', $store) }}" class="flex items-end gap-2">
                            @csrf
                            <div>
                                <label class="label !mb-1.5 !text-[10px] !text-muted">تجاوز +N طلب</label>
                                <input type="number" name="order_limit_override" min="0" max="1000" value="{{ $row['override'] ?: '' }}" placeholder="0" class="input w-28 !py-2 !text-xs" dir="ltr">
                            </div>
                            <button class="btn-secondary btn-sm">حفظ</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="card p-10 text-center">
                <p class="text-sm font-bold text-ink">لا توجد متاجر بعد.</p>
                <p class="mt-1 text-[11px] text-muted">أنشئي أول متجر من زر «متجر جديد» في الأعلى.</p>
            </div>
        @endforelse
    </div>
@endsection
