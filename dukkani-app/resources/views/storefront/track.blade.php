@extends('layouts.storefront')

@section('title', 'متابعة الطلب '.$order->number)

@section('content')
    @php
        $isDelivery = $order->fulfillment_type->value === 'delivery';
        $headline = match ($order->status) {
            \App\Enums\OrderStatus::New => 'وصلنا طلبك، وبانتظار التأكيد',
            \App\Enums\OrderStatus::Confirmed => 'تم تأكيد طلبك',
            \App\Enums\OrderStatus::InProduction => 'طلبك قيد التحضير',
            \App\Enums\OrderStatus::Ready => $isDelivery ? 'طلبك جاهز للتوصيل' : 'طلبك جاهز للاستلام',
            \App\Enums\OrderStatus::OutForDelivery => 'طلبك في الطريق إليك',
            \App\Enums\OrderStatus::Delivered => 'تم تسليم طلبك',
            \App\Enums\OrderStatus::Cancelled => 'تم إلغاء هذا الطلب',
        };
    @endphp

    <section class="mx-auto max-w-5xl px-5 pt-8 sm:px-8 sm:pt-10">
        <nav class="flex items-center gap-1.5 text-[10px] font-medium text-muted/70">
            <a href="{{ route('storefront.home', $store) }}" class="transition hover:text-brand">الرئيسية</a>
            <svg class="size-3 text-muted/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
            <span class="text-ink-soft">متابعة الطلب</span>
        </nav>

        <div class="relative mt-5 overflow-hidden rounded-[2.5rem] border {{ $cancelled ? 'border-danger/20 bg-danger-soft/50' : 'border-sage/20 bg-gradient-to-l from-sage-soft/80 via-surface to-brand-soft/60' }} p-6 sm:p-8">
            <div class="absolute -end-10 -top-14 size-44 rounded-full {{ $cancelled ? 'bg-danger/10' : 'bg-sage/10' }} blur-2xl"></div>

            <div class="relative flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                <div class="flex items-start gap-4">
                    <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl {{ $cancelled ? 'bg-danger/10 text-danger' : 'bg-white/70 text-sage' }} shadow-sm">
                        @if ($cancelled)
                            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        @elseif ($order->status === \App\Enums\OrderStatus::Delivered)
                            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        @elseif ($order->status === \App\Enums\OrderStatus::OutForDelivery)
                            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                        @else
                            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        @endif
                    </span>

                    <div>
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] {{ $cancelled ? 'text-danger/70' : 'text-sage' }}">متابعة الطلب</p>
                        <h1 class="mt-1.5 font-display text-[1.8rem] font-bold leading-tight text-ink sm:text-[2.2rem]">{{ $headline }}</h1>
                        <p class="mt-1.5 max-w-md text-xs leading-6 text-muted">{{ $order->status->description() }}</p>
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            <span class="rounded-full bg-white/70 px-3 py-1 font-mono text-[10px] font-bold text-ink" dir="ltr">{{ $order->number }}</span>
                            <x-status-badge :status="$order->status" />
                        </div>
                    </div>
                </div>

                <div class="flex shrink-0 flex-wrap items-center gap-2">
                    <button type="button" x-data="{ copied: false }"
                            @click="navigator.clipboard?.writeText(@js($trackingUrl)).then(() => { copied = true; setTimeout(() => copied = false, 2000); }).catch(() => {})"
                            class="btn-secondary btn-sm bg-white/70">
                        <svg x-show="!copied" class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.888A2.25 2.25 0 0 0 13.5 2.25h-3c-1.03 0-1.9.693-2.166 1.638m7.332 0c.055.194.084.4.084.612v0a.75.75 0 0 1-.75.75H9a.75.75 0 0 1-.75-.75v0c0-.212.03-.418.084-.612m7.332 0c.646.049 1.288.11 1.927.184 1.1.128 1.907 1.077 1.907 2.185V19.5a2.25 2.25 0 0 1-2.25 2.25H6.75A2.25 2.25 0 0 1 4.5 19.5V6.257c0-1.108.806-2.057 1.907-2.185a48.208 48.208 0 0 1 1.927-.184"/></svg>
                        <svg x-show="copied" x-cloak class="size-4 text-sage" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        <span x-text="copied ? 'تم نسخ الرابط' : 'نسخ رابط المتابعة'">نسخ رابط المتابعة</span>
                    </button>
                    <button type="button" onclick="window.print()" class="btn-ghost btn-sm bg-white/60">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z"/></svg>
                        طباعة
                    </button>
                </div>
            </div>
        </div>

        <div class="mt-5 grid gap-5 lg:grid-cols-[1.05fr_0.95fr] lg:items-start">
            <div class="space-y-4">
                <div class="card p-5 sm:p-6">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-bold text-ink">مسار الطلب</h2>
                        <span class="text-[9px] text-muted/60">آخر تحديث {{ $order->updated_at->diffForHumans() }}</span>
                    </div>

                    @if ($cancelled)
                        <div class="mt-4 flex items-start gap-2.5 rounded-2xl bg-danger-soft px-4 py-3 text-[10px] leading-5 text-danger">
                            <svg class="mt-0.5 size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            <span>
                                تم إلغاء هذا الطلب{{ $cancelledAt ? ' في '.$cancelledAt->translatedFormat('j F Y، g:i A') : '' }}.
                                لأي استفسار، تواصلي مع المتجر مباشرة.
                            </span>
                        </div>
                    @endif

                    <ol class="mt-5">
                        @foreach ($steps as $index => $step)
                            @php
                                $value = $step->value;
                                $time = $eventTimes[$value] ?? null;
                                $reached = array_key_exists($value, $eventTimes) || (! $cancelled && $order->status === $step);
                                $isCurrent = ! $cancelled && $order->status === $step;
                                $nextReached = isset($steps[$index + 1]) && array_key_exists($steps[$index + 1]->value, $eventTimes);
                            @endphp

                            <li class="flex gap-3.5 {{ $loop->last ? '' : 'pb-6' }}">
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

                    @if ($order->status === \App\Enums\OrderStatus::New && ! $cancelled)
                        <div class="mt-4 rounded-2xl bg-gold-soft/60 px-4 py-3 text-[10px] leading-5 text-[#8a672c]">
                            سنتواصل معك على واتساب لتأكيد الطلب خلال ساعات العمل. تأكدي من أن رقم هاتفك متاح.
                        </div>
                    @endif
                </div>

                <div class="card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-line/70 px-5 py-4">
                        <h2 class="text-sm font-bold text-ink">تفاصيل الطلب</h2>
                        <span class="text-[9px] text-muted/60">{{ $order->items->sum('quantity') }} قطعة</span>
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
                                            <p class="mt-0.5 text-[9px] text-muted">الكمية: {{ $item->quantity }} × {{ \App\Support\Money::format($item->unit_price_fils) }}</p>
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
                            <span class="font-bold text-ink">{{ $order->delivery_fee_fils > 0 ? \App\Support\Money::format($order->delivery_fee_fils) : 'مجاني' }}</span>
                        </div>
                        @if ($order->discount_fils > 0)
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted">الخصم</span>
                                <span class="font-bold text-sage">- {{ \App\Support\Money::format($order->discount_fils) }}</span>
                            </div>
                        @endif
                        <div class="divider"></div>
                        <div class="flex items-center justify-between gap-3 pt-1">
                            <span class="text-xs font-bold text-ink">الإجمالي</span>
                            <span class="font-display text-lg font-bold text-brand">{{ $order->totalFormatted() }}</span>
                        </div>
                        @if ($order->deposit_fils > 0)
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted">العربون ({{ $order->deposit_status->label() }})</span>
                                <span class="font-bold text-ink">{{ $order->depositFormatted() }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted">المتبقي عند الاستلام</span>
                                <span class="font-bold text-ink">{{ $order->balanceFormatted() }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($order->deposit_fils > 0 && $order->deposit_status === \App\Enums\DepositStatus::Pending)
                    <div class="rounded-[2rem] border border-gold/30 bg-gold-soft/50 p-5">
                        <div class="flex items-start gap-3">
                            <span class="flex size-10 shrink-0 items-center justify-center rounded-2xl bg-white/70 text-[#a47b32]">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                            </span>
                            <div class="min-w-0 flex-1">
                                <p class="text-xs font-bold text-ink">عربون الطلب بانتظار التحويل</p>
                                <p class="mt-1 text-[10px] leading-6 text-[#8a672c]">{{ $store->deposit_instructions }}</p>
                                <div class="mt-3 flex flex-wrap items-center gap-3">
                                    <span class="rounded-full bg-white/70 px-3 py-1 text-[10px] font-bold text-ink">المبلغ: {{ $order->depositFormatted() }}</span>
                                    <a href="{{ $store->whatsappLink("مرحباً، أرسل صورة تحويل عربون الطلب {$order->number}.") }}" target="_blank" rel="noopener" class="btn-primary btn-sm">
                                        أرسلي صورة التحويل
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <div class="space-y-4">
                <div class="card p-5 sm:p-6">
                    <h2 class="text-sm font-bold text-ink">بيانات الطلب</h2>
                    <div class="mt-4 space-y-3.5">
                        <div class="flex items-start gap-3">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-canvas text-muted"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg></span>
                            <div>
                                <p class="text-[9px] text-muted/70">الموعد</p>
                                <p class="mt-0.5 text-[11px] font-bold text-ink">{{ $order->scheduled_at->translatedFormat('l، j F Y') }}</p>
                                <p class="mt-0.5 text-[10px] text-muted">{{ $order->scheduled_at->translatedFormat('g:i A') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-canvas text-muted">
                                @if ($isDelivery)
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                                @else
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                @endif
                            </span>
                            <div class="min-w-0">
                                <p class="text-[9px] text-muted/70">{{ $order->fulfillment_type->label() }}</p>
                                <p class="mt-0.5 text-[11px] leading-5 text-ink">
                                    {{ $isDelivery ? $order->delivery_address : $store->address.'، '.$store->city }}
                                </p>
                                @unless ($isDelivery)
                                    <a href="{{ $store->mapsUrl() }}" target="_blank" rel="noopener" class="mt-1 inline-flex items-center gap-1 text-[9px] font-bold text-brand">افتحي الموقع <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg></a>
                                @endunless
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-canvas text-muted"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg></span>
                            <div>
                                <p class="text-[9px] text-muted/70">طريقة الدفع</p>
                                <p class="mt-0.5 text-[11px] font-bold text-ink">{{ $order->payment_method?->label() ?? 'غير محدد' }}</p>
                                @if ($order->deposit_fils > 0)
                                    <p class="mt-0.5 text-[10px] text-muted">العربون: {{ $order->deposit_status->label() }}</p>
                                @endif
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-canvas text-muted"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/></svg></span>
                            <div>
                                <p class="text-[9px] text-muted/70">العميل</p>
                                <p class="mt-0.5 text-[11px] font-bold text-ink">{{ $order->customer->name }}</p>
                                <p class="mt-0.5 text-[10px] text-muted" dir="ltr">{{ $maskedPhone }}</p>
                            </div>
                        </div>

                        @if ($order->customer_notes)
                            <div class="rounded-2xl bg-canvas/70 px-3.5 py-3">
                                <p class="text-[9px] font-bold text-muted/70">ملاحظاتك</p>
                                <p class="mt-1 text-[10px] leading-5 text-ink-soft">{{ $order->customer_notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card p-5 sm:p-6">
                    <h2 class="text-sm font-bold text-ink">نساعدك بشيء؟</h2>
                    <p class="mt-1.5 text-[10px] leading-5 text-muted">لأي تعديل على الطلب أو سؤال عن موعده، راسلينا على واتساب مع رقم الطلب.</p>
                    <a href="{{ $whatsappLink }}" target="_blank" rel="noopener" class="btn-primary mt-4 w-full">
                        <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                        تواصلي مع المتجر
                    </a>
                    <a href="{{ route('storefront.menu', $store) }}" class="btn-secondary mt-2 w-full">العودة إلى المنيو</a>
                </div>

                <div class="rounded-2xl border border-line/70 bg-surface px-4 py-3 text-[10px] leading-5 text-muted">
                    <span class="font-bold text-ink">رابط المتابعة:</span> احتفظي بهذا الرابط لمعرفة حالة طلبك في أي وقت. هذا عرض تجريبي، والبيانات وهمية.
                </div>
            </div>
        </div>
    </section>
@endsection