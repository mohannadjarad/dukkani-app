@extends('layouts.dashboard')

@section('title', 'الطلب '.$order->number)
@section('page-title', 'تفاصيل الطلب')

@section('content')
    @php
        use App\Enums\DepositStatus;
        use App\Enums\OrderStatus;
        use App\Support\Money;

        $cancelled = $order->status === OrderStatus::Cancelled;
        $isDelivery = $order->fulfillment_type->value === 'delivery';
        $nextStatuses = collect($order->status->nextStatuses());
        $advance = $nextStatuses->reject(fn ($status) => $status === OrderStatus::Cancelled);
        $canCancel = $nextStatuses->contains(OrderStatus::Cancelled);
        $trackingUrl = $order->publicUrl();
        $eventTones = [
            'brand' => 'bg-brand-soft text-brand',
            'emerald' => 'bg-sage-soft text-sage',
            'gold' => 'bg-gold-soft text-[#a47b32]',
            'sage' => 'bg-sage-soft text-sage',
            'stone' => 'bg-canvas text-muted',
        ];
        $eventIcons = [
            'sparkles' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>',
            'arrow' => '<path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992V4.356M2.985 19.644v-4.992h4.992m-4.587-2.29a8.25 8.25 0 0 1 13.803-3.7l3.182 3.182M2.625 14.25l3.182 3.182a8.25 8.25 0 0 0 13.803-3.7"/>',
            'cash' => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/>',
            'undo' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 15 3 9m0 0 6-6M3 9h12a6 6 0 0 1 0 12h-3"/>',
            'note' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z"/>',
            'chat' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z"/>',
            'user' => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>',
            'dot' => '<circle cx="12" cy="12" r="3"/>',
        ];
    @endphp

    <nav class="flex items-center gap-1.5 text-[10px] font-medium text-muted/70">
        <a href="{{ route('orders.index') }}" class="transition hover:text-brand">الطلبات</a>
        <svg class="size-3 text-muted/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
        <span class="font-mono text-ink-soft" dir="ltr">{{ $order->number }}</span>
    </nav>

    <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <div class="flex flex-wrap items-center gap-2.5">
                <h1 class="font-mono text-xl font-bold text-ink" dir="ltr">{{ $order->number }}</h1>
                <x-status-badge :status="$order->status" />
            </div>
            <p class="mt-1.5 text-xs leading-6 text-muted">
                أُنشئ {{ $order->created_at->translatedFormat('j F Y، g:i A') }}
                • الموعد {{ $order->scheduled_at->translatedFormat('j F Y، g:i A') }}
                • {{ $order->fulfillment_type->label() }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <button type="button" x-data="{ copied: false }"
                    @click="navigator.clipboard?.writeText(@js($trackingUrl)).then(() => { copied = true; setTimeout(() => copied = false, 2000); }).catch(() => {})"
                    class="btn-secondary btn-sm">
                <svg x-show="!copied" class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/></svg>
                <svg x-show="copied" x-cloak class="size-3.5 text-sage" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                <span x-text="copied ? 'تم النسخ' : 'نسخ رابط العميل'">نسخ رابط العميل</span>
            </button>
            <a href="{{ route('track.show', $order->public_token) }}" target="_blank" rel="noopener" class="btn-secondary btn-sm">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg>
                صفحة العميل
            </a>
        </div>
    </div>

    <div class="mt-5 grid gap-4 lg:grid-cols-[1.15fr_0.85fr] lg:items-start">
        <div class="space-y-4">
            <div class="card p-5 sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="text-sm font-bold text-ink">سير العمل</h2>
                    @if ($order->status->isFinal())
                        <span class="text-[10px] text-muted">الطلب مغلق — {{ $order->status->label() }}</span>
                    @else
                        <span class="text-[10px] text-muted">انقليه عبر الحالات الموضحة أدناه</span>
                    @endif
                </div>

                @if ($advance->isNotEmpty() || $canCancel)
                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        @foreach ($advance as $status)
                            <form method="POST" action="{{ route('orders.status', $order) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ $status->value }}">
                                <button class="{{ $loop->first ? 'btn-primary' : 'btn-secondary' }} btn-sm">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
                                    {{ $status->label() }}
                                </button>
                            </form>
                        @endforeach

                        @if ($canCancel)
                            <form method="POST" action="{{ route('orders.status', $order) }}" onsubmit="return confirm('هل تريدين إلغاء الطلب {{ $order->number }}؟')" class="ms-auto">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="{{ OrderStatus::Cancelled->value }}">
                                <button class="btn-ghost btn-sm text-danger">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    إلغاء الطلب
                                </button>
                            </form>
                        @endif
                    </div>
                @endif

                @if ($cancelled)
                    <div class="mt-4 flex items-start gap-2.5 rounded-2xl bg-danger-soft px-4 py-3 text-[10px] leading-5 text-danger">
                        <svg class="mt-0.5 size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        <span>{{ $order->status->description() }}</span>
                    </div>
                @else
                    <ol class="mt-5">
                        @foreach ($steps as $index => $step)
                            @php
                                $value = $step->value;
                                $time = $eventTimes[$value] ?? null;
                                $reached = array_key_exists($value, $eventTimes) || $order->status === $step;
                                $isCurrent = $order->status === $step;
                                $nextReached = isset($steps[$index + 1]) && array_key_exists($steps[$index + 1]->value, $eventTimes);
                            @endphp

                            <li class="flex gap-3.5 {{ $loop->last ? '' : 'pb-5' }}">
                                <div class="flex flex-col items-center">
                                    <span class="flex size-8 shrink-0 items-center justify-center rounded-full {{ $reached ? 'bg-brand text-white' : 'bg-canvas text-muted/40' }} {{ $isCurrent ? 'ring-4 ring-brand/15' : '' }}">
                                        @if ($reached)
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                                        @else
                                            <span class="size-1.5 rounded-full bg-current"></span>
                                        @endif
                                    </span>
                                    @unless ($loop->last)
                                        <span class="mt-1 w-0.5 flex-1 {{ $reached && $nextReached ? 'bg-brand/30' : 'bg-line' }}"></span>
                                    @endunless
                                </div>

                                <div class="min-w-0 flex-1 pt-1">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <p class="text-xs font-bold {{ $reached ? 'text-ink' : 'text-muted/55' }}">{{ $step->label() }}</p>
                                        @if ($isCurrent)
                                            <span class="rounded-full bg-brand-soft px-2 py-0.5 text-[8px] font-bold text-brand-dark">الحالة الحالية</span>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 text-[10px] leading-5 {{ $reached ? 'text-muted' : 'text-muted/45' }}">{{ $step->description() }}</p>
                                    @if ($time)
                                        <p class="mt-1 text-[9px] text-muted/55">{{ $time->translatedFormat('j F Y، g:i A') }}</p>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                @endif
            </div>

            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-line/70 px-5 py-4">
                    <h2 class="text-sm font-bold text-ink">الأصناف</h2>
                    <span class="text-[9px] text-muted/60">{{ $order->itemsCount() }} قطعة</span>
                </div>
                <div class="divide-y divide-line/60">
                    @foreach ($order->items as $item)
                        <div class="flex gap-3.5 px-5 py-4">
                            <img src="{{ $item->product?->imageUrl() ?? asset('images/placeholder-bakery.svg') }}"
                                 alt="{{ $item->product_name }}" class="size-14 shrink-0 rounded-2xl object-cover">
                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-xs font-bold text-ink">{{ $item->product_name }}</p>
                                        <p class="mt-0.5 text-[9px] text-muted">الكمية: {{ $item->quantity }} × {{ Money::format($item->unit_price_fils) }}</p>
                                        @if ($item->optionsLabel())
                                            <p class="mt-1 text-[9px] leading-5 text-muted/70">{{ $item->optionsLabel() }}</p>
                                        @endif
                                        @if ($item->notes)
                                            <p class="mt-1 rounded-lg bg-gold-soft/40 px-2 py-1 text-[9px] leading-5 text-[#8a672c]">{{ $item->notes }}</p>
                                        @endif
                                    </div>
                                    <span class="shrink-0 text-xs font-bold text-ink">{{ $item->lineTotalFormatted() }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="space-y-2.5 border-t border-line/70 bg-canvas/40 px-5 py-4 text-[11px]">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-muted">المجموع الفرعي</span>
                        <span class="font-bold text-ink">{{ $order->subtotalFormatted() }}</span>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-muted">{{ $isDelivery ? 'التوصيل' : 'الاستلام من المتجر' }}</span>
                        <span class="font-bold text-ink">{{ $order->delivery_fee_fils > 0 ? Money::format($order->delivery_fee_fils) : 'مجاني' }}</span>
                    </div>
                    @if ($order->discount_fils > 0)
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted">الخصم</span>
                            <span class="font-bold text-sage">- {{ Money::format($order->discount_fils) }}</span>
                        </div>
                    @endif
                    <div class="divider"></div>
                    <div class="flex items-center justify-between gap-3 pt-1">
                        <span class="text-xs font-bold text-ink">الإجمالي</span>
                        <span class="font-display text-lg font-bold text-brand">{{ $order->totalFormatted() }}</span>
                    </div>
                    @if ($order->deposit_fils > 0)
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted">العربون</span>
                            <span class="font-bold text-ink">{{ $order->depositFormatted() }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted">المتبقي</span>
                            <span class="font-bold text-ink">{{ $order->balanceFormatted() }}</span>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card p-5 sm:p-6">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-ink">ملاحظات داخلية</h2>
                    <span class="rounded-full bg-canvas px-2 py-0.5 text-[9px] font-bold text-muted">لا تظهر للعميل</span>
                </div>
                <form method="POST" action="{{ route('orders.notes', $order) }}" class="mt-3">
                    @csrf
                    @method('PATCH')
                    <textarea name="internal_notes" rows="3" maxlength="1000" class="textarea" placeholder="مثال: العميل حساس من المكسرات — تم التأكيد.">{{ old('internal_notes', $order->internal_notes) }}</textarea>
                    @error('internal_notes') <p class="field-error">{{ $message }}</p> @enderror
                    <div class="mt-3 flex justify-end">
                        <button class="btn-secondary btn-sm">حفظ الملاحظة</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="space-y-4">
            <div class="card p-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-ink">العربون</h2>
                    @if ($order->deposit_fils > 0)
                        <x-deposit-badge :status="$order->deposit_status" />
                    @endif
                </div>

                @if ($order->deposit_fils > 0)
                    <div class="mt-4 space-y-2.5 text-[11px]">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted">قيمة العربون</span>
                            <span class="font-bold text-ink">{{ $order->depositFormatted() }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted">طريقة الدفع</span>
                            <span class="font-bold text-ink">{{ $order->payment_method?->label() ?? '—' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted">المبلغ المتبقي</span>
                            <span class="font-bold text-ink">{{ $order->balanceFormatted() }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-2">
                        @if ($order->deposit_status === DepositStatus::Pending)
                            <form method="POST" action="{{ route('orders.deposit', $order) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="deposit_status" value="{{ DepositStatus::Paid->value }}">
                                <button class="btn-primary btn-sm">تأكيد استلام العربون</button>
                            </form>
                        @endif

                        @if ($order->deposit_status === DepositStatus::Paid)
                            <form method="POST" action="{{ route('orders.deposit', $order) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="deposit_status" value="{{ DepositStatus::Refunded->value }}">
                                <button class="btn-secondary btn-sm text-danger">استرجاع العربون</button>
                            </form>
                        @endif

                        @if ($order->deposit_status !== DepositStatus::Pending)
                            <form method="POST" action="{{ route('orders.deposit', $order) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="deposit_status" value="{{ DepositStatus::Pending->value }}">
                                <button class="btn-ghost btn-sm">إعادة إلى معلّق</button>
                            </form>
                        @endif
                    </div>
                @else
                    <p class="mt-4 text-[10px] leading-5 text-muted">
                        هذا الطلب بدون عربون (الدفع نقداً عند {{ $isDelivery ? 'التوصيل' : 'الاستلام' }}).
                    </p>
                @endif
            </div>

            <div class="card p-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-ink">العميل</h2>
                    @if ($order->customer->isRepeat())
                        <span class="rounded-full bg-brand-soft px-2.5 py-1 text-[9px] font-bold text-brand-dark">عميل متكرر</span>
                    @endif
                </div>
                <div class="mt-4 flex items-center gap-3">
                    <x-avatar :name="$order->customer->name" size="lg" />
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-ink">{{ $order->customer->name }}</p>
                        <p class="mt-0.5 text-[10px] text-muted" dir="ltr">{{ $order->customer->phone }}</p>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div class="rounded-2xl bg-canvas/70 px-3 py-2.5">
                        <p class="text-[9px] text-muted/70">عدد الطلبات</p>
                        <p class="mt-0.5 text-sm font-bold text-ink">{{ $order->customer->orders()->count() }}</p>
                    </div>
                    <div class="rounded-2xl bg-canvas/70 px-3 py-2.5">
                        <p class="text-[9px] text-muted/70">إجمالي الشراء</p>
                        <p class="mt-0.5 text-sm font-bold text-ink">{{ Money::format($order->customer->totalSpent()) }}</p>
                    </div>
                </div>
                @if (! empty($order->customer->tags))
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach ($order->customer->tags as $tag)
                            <span class="rounded-full border border-line bg-surface px-2.5 py-1 text-[9px] font-bold text-muted">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
                <div class="mt-4 grid grid-cols-2 gap-2">
                    <a href="tel:{{ preg_replace('/\s+/', '', $order->customer->phone) }}" class="btn-secondary btn-sm">اتصال</a>
                    @if ($wa = $order->customer->whatsappLink('مرحباً '.$order->customer->name.'، بخصوص طلبك '.$order->number.'.'))
                        <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn-secondary btn-sm">واتساب</a>
                    @else
                        <span class="btn-secondary btn-sm cursor-not-allowed opacity-50" title="لا يوجد رقم للعميل">واتساب</span>
                    @endif
                </div>

                <div class="mt-4 flex items-start gap-2.5 rounded-2xl bg-canvas/60 px-3.5 py-3">
                    <span class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-white text-muted">
                        @if ($isDelivery)
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                        @else
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/></svg>
                        @endif
                    </span>
                    <div class="min-w-0">
                        <p class="text-[9px] text-muted/70">{{ $isDelivery ? 'عنوان التوصيل' : 'الاستلام من المتجر' }}</p>
                        <p class="mt-0.5 text-[10px] leading-5 text-ink-soft">
                            {{ $isDelivery ? ($order->delivery_address ?: '—') : ($store->address.'، '.$store->city) }}
                        </p>
                    </div>
                </div>

                @if ($order->customer_notes)
                    <div class="mt-3 rounded-2xl border border-gold/30 bg-gold-soft/40 px-3.5 py-3">
                        <p class="text-[9px] font-bold text-[#8a672c]">ملاحظات العميل</p>
                        <p class="mt-1 text-[10px] leading-5 text-[#8a672c]">{{ $order->customer_notes }}</p>
                    </div>
                @endif
            </div>

            <div class="card p-5 sm:p-6">
                <div class="flex items-center gap-2">
                    <h2 class="text-sm font-bold text-ink">رسائل واتساب</h2>
                    <span class="demo-pill !px-2 !py-0.5 !text-[9px]">تجريبي</span>
                </div>
                <p class="mt-1.5 text-[10px] leading-5 text-muted">اختاري قالباً ليُفتح واتساب برسالة جاهزة، مع تسجيل الرسالة في سجل الطلب.</p>

                <form id="msg-form-{{ $order->id }}" method="POST" action="{{ route('orders.messages.store', $order) }}" class="hidden">
                    @csrf
                    <input type="hidden" name="template" id="msg-template-{{ $order->id }}">
                </form>

                <div class="mt-4 space-y-2">
                    @foreach ($templates as $template)
                        <a href="{{ $template['url'] }}" target="_blank" rel="noopener"
                           onclick="document.getElementById('msg-template-{{ $order->id }}').value='{{ $template['key'] }}';document.getElementById('msg-form-{{ $order->id }}').submit();"
                           class="flex items-center justify-between gap-2 rounded-2xl border border-line/70 px-3.5 py-2.5 transition hover:border-brand/30 hover:bg-canvas/60">
                            <div class="min-w-0">
                                <p class="text-[11px] font-bold text-ink">{{ $template['label'] }}</p>
                                <p class="mt-0.5 truncate text-[9px] text-muted">{{ $template['summary'] }}</p>
                            </div>
                            <svg class="size-4 shrink-0 text-sage" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                        </a>
                    @endforeach
                </div>

                @if ($order->messages->isNotEmpty())
                    <div class="mt-4 border-t border-line/60 pt-3">
                        <p class="text-[10px] font-bold text-muted">آخر الرسائل المسجّلة</p>
                        <div class="mt-2 space-y-2">
                            @foreach ($order->messages->sortByDesc('sent_at')->take(4) as $message)
                                <div class="flex items-center justify-between gap-2 text-[10px]">
                                    <span class="text-ink-soft">{{ $message->templateLabel() }}</span>
                                    <span class="text-muted/60">{{ optional($message->sent_at)->diffForHumans() ?? $message->created_at->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <div class="card p-5 sm:p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-bold text-ink">سجل النشاط</h2>
                    <span class="text-[9px] text-muted/60">{{ $order->events->count() }} حدث</span>
                </div>

                <ol class="mt-4 space-y-0">
                    @forelse ($order->events as $index => $event)
                        <li class="flex gap-3 {{ $loop->last ? '' : 'pb-4' }}">
                            <div class="flex flex-col items-center">
                                <span class="flex size-7 shrink-0 items-center justify-center rounded-full {{ $eventTones[$event->color()] ?? 'bg-canvas text-muted' }}">
                                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor">{!! $eventIcons[$event->icon()] ?? $eventIcons['dot'] !!}</svg>
                                </span>
                                @unless ($loop->last)
                                    <span class="mt-1 w-0.5 flex-1 bg-line"></span>
                                @endunless
                            </div>
                            <div class="min-w-0 flex-1 pt-0.5">
                                <p class="text-[11px] font-bold text-ink">{{ $event->label() }}</p>
                                @if ($event->detail())
                                    <p class="mt-0.5 text-[10px] leading-5 text-muted">{{ $event->detail() }}</p>
                                @endif
                                <p class="mt-0.5 text-[9px] text-muted/60">
                                    {{ $event->created_at->translatedFormat('j F Y، g:i A') }}
                                    @if ($event->user) • {{ $event->user->name }} @endif
                                </p>
                            </div>
                        </li>
                    @empty
                        <p class="text-[10px] text-muted">لا يوجد نشاط بعد.</p>
                    @endforelse
                </ol>
            </div>
        </div>
    </div>
@endsection
