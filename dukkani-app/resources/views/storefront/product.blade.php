@extends('layouts.storefront')

@section('title', $product->name)

@section('content')
    <section class="mx-auto max-w-6xl px-5 pt-8 sm:px-8 sm:pt-10">
        <nav class="flex flex-wrap items-center gap-1.5 text-[10px] font-medium text-muted/70">
            <a href="{{ route('storefront.home', $store) }}" class="transition hover:text-brand">الرئيسية</a>
            <svg class="size-3 text-muted/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
            <a href="{{ route('storefront.menu', $store) }}" class="transition hover:text-brand">المنيو</a>
            @if ($product->category)
                <svg class="size-3 text-muted/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
                <a href="{{ route('storefront.menu', ['store' => $store, 'category' => $product->category->id]) }}" class="transition hover:text-brand">{{ $product->category->name }}</a>
            @endif
            <svg class="size-3 text-muted/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
            <span class="text-ink-soft">{{ $product->name }}</span>
        </nav>

        <div class="mt-5 grid gap-8 lg:grid-cols-[1fr_1fr] lg:gap-12">
            <div class="lg:sticky lg:top-24 lg:self-start">
                <div class="relative overflow-hidden rounded-[2.5rem] bg-canvas shadow-[var(--shadow-soft)]">
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
                    <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-2 p-4">
                        @if ($product->is_featured)
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gold-soft/95 px-3 py-1.5 text-[10px] font-bold text-[#8a672c] shadow-sm backdrop-blur">
                                <span class="size-1.5 rounded-full bg-gold"></span>
                                الأكثر طلباً
                            </span>
                        @else
                            <span></span>
                        @endif
                        @if ($product->category)
                            <span class="rounded-full bg-white/90 px-3 py-1.5 text-[10px] font-bold text-ink-soft shadow-sm backdrop-blur">{{ $product->category->name }}</span>
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-2.5">
                    @foreach ([
                        ['طازج', 'يُخبز بعد التأكيد', 'M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5'],
                        ['تحضير', ($product->lead_time_hours ?: $store->lead_time_hours).' ساعة', 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                        ['تغليف', 'بعناية وهدية', 'M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H4.5a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12'],
                    ] as [$title, $subtitle, $icon])
                        <div class="rounded-2xl border border-line/70 bg-surface px-3 py-3 text-center">
                            <svg class="mx-auto size-4 text-brand" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                            <p class="mt-1.5 text-[10px] font-bold text-ink">{{ $title }}</p>
                            <p class="mt-0.5 text-[9px] text-muted">{{ $subtitle }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div x-data="productForm(@js($productConfig))">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand">{{ $product->category?->name ?? 'من المنيو' }}</p>
                <h1 class="mt-2 font-display text-[2.1rem] font-bold leading-tight text-ink text-balance sm:text-[2.6rem]">{{ $product->name }}</h1>

                <div class="mt-3 flex flex-wrap items-center gap-3">
                    <span class="font-display text-2xl font-bold text-brand" x-text="money(unit)">{{ $product->priceFormatted() }}</span>
                    <span class="h-4 w-px bg-line"></span>
                    <span class="flex items-center gap-1.5 text-[10px] text-muted">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                        تحضير {{ $product->lead_time_hours ?: $store->lead_time_hours }} ساعة
                    </span>
                    @if ($product->options->isNotEmpty())
                        <span class="flex items-center gap-1.5 text-[10px] text-muted">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75"/></svg>
                            خيارات متاحة
                        </span>
                    @endif
                </div>

                <p class="mt-4 text-sm leading-7 text-muted">{{ $product->description }}</p>

                <form method="POST" action="{{ route('storefront.cart.add', $store) }}" class="mt-6 space-y-5">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    @if ($errors->any())
                        <div class="flex items-start gap-3 rounded-2xl border border-danger/20 bg-danger-soft px-4 py-3.5 text-xs text-danger">
                            <svg class="mt-0.5 size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 2.35 17.6A1.5 1.5 0 0 0 3.65 20h16.7a1.5 1.5 0 0 0 1.3-2.4L13.7 3.86a1.5 1.5 0 0 0-2.6 0Z"/></svg>
                            <span>{{ $errors->first() }}</span>
                        </div>
                    @endif

                    @foreach ($product->options as $option)
                        <div>
                            <div class="mb-2 flex items-center justify-between gap-2">
                                <p class="text-[13px] font-bold text-ink-soft">
                                    {{ $option->name }}
                                    @if ($option->is_required)
                                        <span class="text-brand">*</span>
                                    @endif
                                </p>
                                <span class="text-[9px] text-muted/60">{{ $option->is_required ? 'مطلوب' : 'اختياري' }}</span>
                            </div>

                            <div class="grid gap-2 sm:grid-cols-2">
                                @foreach ($option->values as $value)
                                    <label class="group relative flex cursor-pointer items-start gap-3 rounded-2xl border border-line bg-white p-3.5 transition hover:border-brand/30 has-[:checked]:border-brand has-[:checked]:bg-brand-soft/40">
                                        <input type="radio" name="options[{{ $option->id }}]" value="{{ $value->id }}"
                                               x-model="selected[{{ $option->id }}]"
                                               @if ($option->is_required) required @endif
                                               class="mt-0.5 size-4 shrink-0 border-line text-brand focus:ring-brand/20">
                                        <span class="min-w-0 flex-1">
                                            <span class="block text-xs font-bold text-ink">{{ $value->value }}</span>
                                            <span class="mt-0.5 block text-[10px] text-muted">
                                                {{ $value->price_delta_fils > 0 ? $value->deltaLabel() : 'بدون فرق سعر' }}
                                            </span>
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                            @error('options')
                                <p class="field-error">{{ $message }}</p>
                            @enderror
                        </div>
                    @endforeach

                    <div>
                        <div class="mb-2 flex items-center justify-between">
                            <label for="notes" class="text-[13px] font-bold text-ink-soft">ملاحظات على الطلب</label>
                            <span class="text-[9px] text-muted/60">اختياري</span>
                        </div>
                        <textarea id="notes" name="notes" rows="2" maxlength="200" x-model="notes"
                                  class="textarea min-h-20 text-xs" placeholder="مثلاً: كتابة على الكيك، حساسية من المكسرات، وقت مناسب للتوصيل…"></textarea>
                        <p class="mt-1.5 text-[9px] text-muted/60"><span x-text="200 - notes.length"></span> حرفاً متبقياً</p>
                    </div>

                    <div class="flex flex-col gap-3 rounded-3xl border border-line/80 bg-surface p-4 sm:flex-row sm:items-center">
                        <div class="flex items-center justify-between gap-3 sm:justify-start">
                            <span class="text-[11px] font-bold text-muted">الكمية</span>
                            <div class="flex items-center gap-1 rounded-full border border-line bg-white p-1">
                                <button type="button" @click="qty = Math.max(1, qty - 1)" class="flex size-8 items-center justify-center rounded-full text-ink-soft transition hover:bg-canvas" aria-label="تقليل الكمية">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14"/></svg>
                                </button>
                                <input type="number" name="quantity" min="1" max="20" x-model.number="qty"
                                       class="w-8 border-0 bg-transparent p-0 text-center text-sm font-bold text-ink focus:ring-0" aria-label="الكمية">
                                <button type="button" @click="qty = Math.min(20, qty + 1)" class="flex size-8 items-center justify-center rounded-full text-ink-soft transition hover:bg-canvas" aria-label="زيادة الكمية">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="btn-primary flex-1 justify-between" :disabled="missingRequired">
                            <span class="flex items-center gap-2">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                                أضيفي إلى السلة
                            </span>
                            <span x-text="money(total)">{{ $product->priceFormatted() }}</span>
                        </button>
                    </div>

                    <p x-show="missingRequired" x-cloak class="text-center text-[10px] font-medium text-brand">
                        اختاري الخيارات المطلوبة أولاً (المعلّمة بـ *).
                    </p>
                </form>

                <div class="mt-6 space-y-2.5 rounded-3xl bg-canvas/70 p-4 text-[10px] leading-6 text-muted">
                    <p class="flex items-start gap-2">
                        <svg class="mt-1 size-3.5 shrink-0 text-sage" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        نبدأ التحضير بعد تأكيد الطلب، لذا نرجو الالتزام بأقل مدة تحضير {{ $product->lead_time_hours ?: $store->lead_time_hours }} ساعة.
                    </p>
                    <p class="flex items-start gap-2">
                        <svg class="mt-1 size-3.5 shrink-0 text-sage" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        لأي حساسية أو طلب خاص، اكتبيها في الملاحظات وسنتواصل معك للتأكيد.
                    </p>
                    <p class="flex items-start gap-2">
                        <svg class="mt-1 size-3.5 shrink-0 text-sage" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                        الدفع نقداً عند الاستلام، أو بعربون {{ $store->deposit_percent }}% عبر CliQ للطلبات الخاصة.
                    </p>
                </div>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="mx-auto mt-16 max-w-6xl px-5 sm:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand">قد يعجبك أيضاً</p>
                    <h2 class="mt-2 font-display text-2xl font-bold text-ink">من نفس التصنيف</h2>
                </div>
                <a href="{{ route('storefront.menu', ['store' => $store, 'category' => $product->category_id]) }}" class="hidden items-center gap-1 text-[11px] font-bold text-brand transition hover:gap-2 sm:flex">
                    عرض الكل
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 19.5 12m0 0-9-7.5m9 7.5H3"/></svg>
                </a>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($related as $relatedProduct)
                    <x-storefront.product-card :product="$relatedProduct" :$store />
                @endforeach
            </div>
        </section>
    @endif
@endsection