@extends('layouts.storefront')

@section('title', 'سلة الطلب')

@section('content')
    <section class="mx-auto max-w-6xl px-5 pt-8 sm:px-8 sm:pt-10">
        <nav class="flex items-center gap-1.5 text-[10px] font-medium text-muted/70">
            <a href="{{ route('storefront.home', $store) }}" class="transition hover:text-brand">الرئيسية</a>
            <svg class="size-3 text-muted/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
            <span class="text-ink-soft">السلة</span>
        </nav>

        <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand">خطوة واحدة قبل التأكيد</p>
                <h1 class="mt-2 font-display text-[2.1rem] font-bold leading-tight text-ink sm:text-[2.6rem]">سلة الطلب</h1>
                <p class="mt-2 text-sm leading-7 text-muted">
                    @if ($items->isEmpty())
                        سلتك فارغة الآن — اختاري شيئاً حلوًا من المنيو.
                    @else
                        راجعي طلبك، عدّلي الكميات، وبعدها نكمل بيانات التوصيل أو الاستلام.
                    @endif
                </p>
            </div>

            @if ($items->isNotEmpty())
                <span class="inline-flex items-center gap-2 rounded-full border border-line bg-surface px-4 py-2 text-[11px] font-bold text-muted">
                    <span class="size-1.5 rounded-full bg-brand"></span>
                    {{ $cartCount }} قطعة في السلة
                </span>
            @endif
        </div>

        @if ($items->isEmpty())
            <div class="mt-8 flex flex-col items-center justify-center rounded-[2.5rem] border border-dashed border-line bg-surface/60 px-6 py-16 text-center">
                <span class="flex size-16 items-center justify-center rounded-full bg-canvas text-muted">
                    <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                </span>
                <p class="mt-4 font-display text-xl font-bold text-ink">سلتك فارغة</p>
                <p class="mt-1.5 max-w-sm text-xs leading-6 text-muted">تصفّحي المنيو واختاري ما يناسب مناسبتك. كل طلب يُحضّر طازجاً بعد تأكيده.</p>
                <a href="{{ route('storefront.menu', $store) }}" class="btn-primary mt-5">
                    تصفّحي المنيو
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 19.5 12m0 0-9-7.5m9 7.5H3"/></svg>
                </a>
            </div>
        @else
            <div class="mt-7 grid gap-5 lg:grid-cols-[1.55fr_0.95fr] lg:items-start">
                <div class="card divide-y divide-line/60">
                    @foreach ($items as $item)
                        <div class="flex flex-col gap-4 p-4 sm:flex-row sm:p-5">
                            <a href="{{ route('storefront.product', [$store, $item['product']]) }}" class="shrink-0">
                                <img src="{{ $item['product']->imageUrl() }}" alt="{{ $item['product']->name }}"
                                     class="h-24 w-full rounded-2xl object-cover sm:h-24 sm:w-24">
                            </a>

                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <a href="{{ route('storefront.product', [$store, $item['product']]) }}" class="font-display text-base font-bold text-ink transition hover:text-brand">{{ $item['product']->name }}</a>
                                        @if ($item['product']->category)
                                            <p class="mt-0.5 text-[9px] font-bold text-muted/60">{{ $item['product']->category->name }}</p>
                                        @endif
                                    </div>
                                    <p class="shrink-0 text-sm font-bold text-ink">{{ \App\Support\Money::format($item['line_total_fils']) }}</p>
                                </div>

                                @if (count($item['options']))
                                    <div class="mt-2 flex flex-wrap gap-1.5">
                                        @foreach ($item['options'] as $option)
                                            <span class="inline-flex items-center gap-1 rounded-full bg-canvas px-2 py-1 text-[9px] text-muted">
                                                <span class="font-bold text-ink-soft">{{ $option['name'] }}:</span>
                                                {{ $option['value'] }}
                                                @if (($option['delta'] ?? 0) > 0)
                                                    <span class="text-brand">+{{ \App\Support\Money::format($option['delta']) }}</span>
                                                @endif
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                @if ($item['notes'])
                                    <p class="mt-2 rounded-xl bg-gold-soft/50 px-3 py-1.5 text-[10px] leading-5 text-[#8a672c]">
                                        <span class="font-bold">ملاحظة:</span> {{ $item['notes'] }}
                                    </p>
                                @endif

                                <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                                    <form method="POST" action="{{ route('storefront.cart.update', [$store, $item['key']]) }}"
                                          x-data="{ q: {{ $item['quantity'] }} }" class="flex items-center gap-1 rounded-full border border-line bg-white p-1">
                                        @csrf
                                        @method('PATCH')
                                        <button type="button" @click="q = Math.max(1, q - 1); $nextTick(() => $el.form.requestSubmit())"
                                                class="flex size-8 items-center justify-center rounded-full text-ink-soft transition hover:bg-canvas" aria-label="تقليل الكمية">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                                        </button>
                                        <input type="number" name="quantity" min="1" max="20" x-model.number="q" @change="$el.form.requestSubmit()"
                                               class="w-8 border-0 bg-transparent p-0 text-center text-xs font-bold text-ink focus:ring-0" aria-label="الكمية">
                                        <button type="button" @click="q = Math.min(20, q + 1); $nextTick(() => $el.form.requestSubmit())"
                                                class="flex size-8 items-center justify-center rounded-full text-ink-soft transition hover:bg-canvas" aria-label="زيادة الكمية">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        </button>
                                    </form>

                                    <div class="flex items-center gap-3">
                                        <span class="text-[10px] text-muted/70" dir="rtl">{{ \App\Support\Money::format($item['unit_price_fils']) }} / للقطعة</span>
                                        <form method="POST" action="{{ route('storefront.cart.remove', [$store, $item['key']]) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="flex items-center gap-1 text-[10px] font-bold text-danger/80 transition hover:text-danger">
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                                حذف
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="lg:sticky lg:top-24">
                    <div class="card p-5 sm:p-6">
                        <div class="flex items-center justify-between">
                            <h2 class="text-sm font-bold text-ink">ملخص الطلب</h2>
                            <span class="rounded-full bg-canvas px-2.5 py-1 text-[9px] font-bold text-muted">{{ $cartCount }} قطعة</span>
                        </div>

                        <div class="mt-5 space-y-3 text-xs">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted">المجموع الفرعي</span>
                                <span class="font-bold text-ink">{{ \App\Support\Money::format($subtotal) }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-muted">التوصيل <span class="text-[9px] text-muted/60">(داخل {{ $store->city }})</span></span>
                                <span class="font-bold text-ink">{{ $deliveryFee > 0 ? \App\Support\Money::format($deliveryFee) : 'مجاني' }}</span>
                            </div>
                            <div class="rounded-xl bg-canvas/70 px-3 py-2 text-[9px] leading-5 text-muted">
                                مجاني عند الاستلام من المتجر. تُحسب رسوم التوصيل النهائية حسب اختيارك في الخطوة التالية.
                            </div>
                            <div class="divider"></div>
                            <div class="flex items-center justify-between gap-3 pt-1">
                                <span class="text-[13px] font-bold text-ink">الإجمالي التقديري</span>
                                <span class="font-display text-xl font-bold text-brand">{{ \App\Support\Money::format($subtotal + $deliveryFee) }}</span>
                            </div>
                        </div>

                        @if ($store->deposit_percent > 0)
                            <div class="mt-4 flex items-start gap-2 rounded-2xl bg-gold-soft/60 px-3.5 py-3 text-[10px] leading-5 text-[#8a672c]">
                                <svg class="mt-0.5 size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                                <span>عند الدفع بـ CliQ أو الحوالة البنكية، يُطلب عربون {{ $store->deposit_percent }}% لتأكيد الطلب. الدفع النقدي عند الاستلام بدون عربون.</span>
                            </div>
                        @endif

                        <a href="{{ route('storefront.checkout', $store) }}" class="btn-primary mt-5 w-full">
                            متابعة إلى البيانات
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 19.5 12m0 0-9-7.5m9 7.5H3"/></svg>
                        </a>

                        <a href="{{ route('storefront.menu', $store) }}" class="btn-ghost mt-1 w-full">متابعة التسوق</a>

                        <form method="POST" action="{{ route('storefront.cart.clear', $store) }}" onsubmit="return confirm('هل تريدين تفريغ السلة بالكامل؟')" class="mt-2 text-center">
                            @csrf
                            <button type="submit" class="text-[10px] font-medium text-muted/60 transition hover:text-danger">تفريغ السلة</button>
                        </form>
                    </div>

                    <div class="mt-3 flex items-start gap-2.5 rounded-2xl bg-sage-soft/60 px-4 py-3 text-[10px] leading-5 text-sage">
                        <svg class="mt-0.5 size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                        <span>بياناتك تُستخدم فقط لتجهيز الطلب. هذا عرض تجريبي ولا تتم أي مدفوعات أو رسائل حقيقية.</span>
                    </div>
                </div>
            </div>

            @if ($suggestions->isNotEmpty())
                <div class="mt-12">
                    <div class="flex items-end justify-between gap-4">
                        <div>
                            <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand">أضيفي لطلبك</p>
                            <h2 class="mt-2 font-display text-2xl font-bold text-ink">اقتراحات سريعة</h2>
                        </div>
                        <a href="{{ route('storefront.menu', $store) }}" class="text-[11px] font-bold text-brand">المنيو الكامل</a>
                    </div>
                    <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($suggestions as $product)
                            <x-storefront.product-card :$product :$store />
                        @endforeach
                    </div>
                </div>
            @endif
        @endif
    </section>
@endsection