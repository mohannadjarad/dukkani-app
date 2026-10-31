@extends('layouts.storefront')

@section('title', 'حلويات منزلية تُخبز على مهل')

@section('content')
    @php
        $today = $store->todayHours();
        $isOpen = $store->isOpenNow();
        $story = trim(explode("\n\n", (string) $store->description)[0] ?? '');
        $cover = $store->coverUrl() ?: asset('images/amal-bakes/store-cover.webp');
    @endphp

    <section class="mx-auto max-w-6xl px-5 pt-6 sm:px-8 sm:pt-8">
        <div class="relative overflow-hidden rounded-[2.5rem] bg-ink shadow-[var(--shadow-lift)]">
            <img src="{{ $cover }}" alt="{{ $store->name }}" class="absolute inset-0 size-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-l from-[#1d110c]/95 via-[#1d110c]/75 to-[#1d110c]/35"></div>
            <div class="absolute inset-0 pattern-dots opacity-20"></div>

            <div class="relative grid gap-10 p-7 sm:p-10 lg:grid-cols-[1.15fr_0.85fr] lg:items-center lg:p-14">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-white/20 bg-white/10 px-3 py-1.5 text-[10px] font-bold text-white/85 backdrop-blur">
                            <span class="size-1.5 rounded-full bg-gold"></span>
                            مخبز منزلي في {{ $store->city }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-bold {{ $isOpen ? 'bg-emerald-400/15 text-emerald-200' : 'bg-white/10 text-white/60' }}">
                            <span class="size-1.5 rounded-full {{ $isOpen ? 'bg-emerald-300' : 'bg-white/40' }}"></span>
                            {{ $isOpen ? 'نستقبل الطلبات الآن' : 'المتجر مغلق حالياً' }}
                        </span>
                    </div>

                    <h1 class="mt-6 max-w-xl font-display text-[2.4rem] font-bold leading-[1.3] text-white text-balance sm:text-[3.1rem]">
                        {{ $store->tagline }}
                    </h1>

                    <p class="mt-5 max-w-lg text-sm leading-7 text-white/65">
                        {{ $story }}
                    </p>

                    <div class="mt-8 flex flex-wrap items-center gap-3">
                        <a href="{{ route('storefront.menu', $store) }}" class="btn-primary bg-white text-ink shadow-none hover:bg-white/90 hover:text-ink">
                            تصفّحي المنيو
                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 19.5 12m0 0-9-7.5m9 7.5H3"/></svg>
                        </a>
                        <a href="{{ $store->whatsappLink('مرحباً، حابة أستفسر عن الطلبات والمواعيد.') }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 rounded-full border border-white/25 bg-white/10 px-5 py-3 text-sm font-bold text-white backdrop-blur transition hover:bg-white/20">
                            <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                            استفسري على واتساب
                        </a>
                    </div>

                    <div class="mt-8 flex flex-wrap gap-x-6 gap-y-3 border-t border-white/15 pt-5 text-[10px] font-medium text-white/55">
                        <span class="flex items-center gap-1.5"><span class="size-1 rounded-full bg-gold"></span> مكونات طازجة وزبدة حقيقية</span>
                        <span class="flex items-center gap-1.5"><span class="size-1 rounded-full bg-gold"></span> تغليف هدايا بعناية</span>
                        <span class="flex items-center gap-1.5"><span class="size-1 rounded-full bg-gold"></span> {{ $store->delivery_enabled ? 'توصيل داخل '.$store->city : 'استلام من المتجر' }}</span>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-white/15 bg-surface/95 p-5 shadow-[var(--shadow-lift)] backdrop-blur sm:p-6">
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-muted/60">بطاقة الطلب</p>
                        <span class="inline-flex items-center gap-1.5 rounded-full {{ $isOpen ? 'bg-sage-soft text-sage' : 'bg-canvas text-muted' }} px-2.5 py-1 text-[9px] font-bold">
                            <span class="size-1.5 rounded-full {{ $isOpen ? 'bg-sage' : 'bg-muted/50' }}"></span>
                            {{ $isOpen ? 'مفتوح' : 'مغلق' }}
                        </span>
                    </div>

                    <p class="mt-4 font-display text-xl font-bold leading-snug text-ink">نُخبز طلبك بعد تأكيده، خصيصاً لك.</p>

                    <div class="mt-5 space-y-3">
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-canvas/70 px-3.5 py-3">
                            <span class="flex items-center gap-2 text-[11px] text-muted">
                                <svg class="size-4 text-brand" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                                أقل مدة تحضير
                            </span>
                            <span class="text-xs font-bold text-ink">{{ $store->lead_time_hours }} ساعة</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-canvas/70 px-3.5 py-3">
                            <span class="flex items-center gap-2 text-[11px] text-muted">
                                <svg class="size-4 text-brand" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                                {{ $store->delivery_enabled ? 'رسوم التوصيل' : 'الاستلام' }}
                            </span>
                            <span class="text-xs font-bold text-ink">{{ $store->delivery_enabled ? \App\Support\Money::format($store->delivery_fee_fils) : 'مجاني' }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3 rounded-2xl bg-canvas/70 px-3.5 py-3">
                            <span class="flex items-center gap-2 text-[11px] text-muted">
                                <svg class="size-4 text-brand" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                                {{ $store->deposit_percent > 0 ? 'عربون التحويل' : 'الدفع' }}
                            </span>
                            <span class="text-xs font-bold text-ink">{{ $store->deposit_percent > 0 ? $store->deposit_percent.'%' : 'نقداً عند الاستلام' }}</span>
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3 rounded-2xl border border-line/80 px-3.5 py-3">
                        <div>
                            <p class="text-[9px] text-muted/70">اليوم — {{ $today?->dayName() }}</p>
                            <p class="mt-0.5 text-[11px] font-bold text-ink" dir="ltr">{{ $today?->label() ?? 'مغلق' }}</p>
                        </div>
                        <a href="{{ $store->mapsUrl() }}" target="_blank" rel="noopener" class="btn-ghost btn-sm text-brand">
                            الموقع
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg>
                        </a>
                    </div>

                    <a href="{{ route('storefront.menu', $store) }}" class="btn-primary mt-4 w-full">
                        ابدئي الطلب
                    </a>
                    <p class="mt-2.5 text-center text-[9px] text-muted/70">الدفع عند الاستلام أو تحويل CliQ — دون دفع إلكتروني</p>
                </div>
            </div>
        </div>
    </section>

    @if ($categories->isNotEmpty())
        <section class="mx-auto mt-12 max-w-6xl px-5 sm:px-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand">تصنيفاتنا</p>
                    <h2 class="mt-2 font-display text-2xl font-bold text-ink">اختاري على ذوقك</h2>
                </div>
                <a href="{{ route('storefront.menu', $store) }}" class="hidden items-center gap-1 text-[11px] font-bold text-brand transition hover:gap-2 sm:flex">
                    المنيو الكامل
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 19.5 12m0 0-9-7.5m9 7.5H3"/></svg>
                </a>
            </div>

            <div class="mt-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ($categories as $category)
                    <a href="{{ route('storefront.menu', ['store' => $store, 'category' => $category->id]) }}"
                       class="group card card-hover flex flex-col justify-between gap-6 p-4 sm:p-5">
                        <div class="flex items-start justify-between">
                            <span class="flex size-10 items-center justify-center rounded-2xl bg-brand-soft text-brand transition group-hover:bg-brand group-hover:text-white">
                                <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 15.75m18-6.75v6.75a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 15.75V9m18 0a1.5 1.5 0 0 0-1.5-1.5H4.5A1.5 1.5 0 0 0 3 9"/></svg>
                            </span>
                            <span class="text-[10px] font-bold text-muted/60">{{ $category->products_count }} منتجات</span>
                        </div>
                        <div>
                            <p class="font-display text-base font-bold text-ink sm:text-lg">{{ $category->name }}</p>
                            <span class="mt-1 inline-flex items-center gap-1 text-[10px] font-bold text-brand opacity-0 transition group-hover:opacity-100">
                                تصفّحي
                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 19.5 12m0 0-9-7.5m9 7.5H3"/></svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto mt-12 max-w-6xl px-5 sm:px-8">
        <div class="flex items-end justify-between gap-4">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand">الأكثر طلباً</p>
                <h2 class="mt-2 font-display text-2xl font-bold text-ink">حلويات يحبها عملاؤنا</h2>
                <p class="mt-1.5 text-[11px] text-muted">مختارات من المنيو، تُحضّر بعد تأكيد طلبك.</p>
            </div>
            <a href="{{ route('storefront.menu', $store) }}" class="hidden items-center gap-1 text-[11px] font-bold text-brand transition hover:gap-2 sm:flex">
                عرض الكل
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 19.5 12m0 0-9-7.5m9 7.5H3"/></svg>
            </a>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($featured as $product)
                <x-storefront.product-card :$product :$store />
            @endforeach
        </div>
    </section>

    <section id="about" class="mx-auto mt-16 max-w-6xl px-5 sm:px-8">
        <div class="grid items-center gap-8 lg:grid-cols-2">
            <div class="relative">
                <div class="overflow-hidden rounded-[2.5rem]">
                    <img src="{{ $cover }}" alt="داخل مطبخ {{ $store->name }}" class="h-[24rem] w-full object-cover sm:h-[28rem]">
                </div>
                <div class="absolute -bottom-6 start-5 max-w-[15rem] rounded-3xl border border-line bg-surface p-4 shadow-[var(--shadow-lift)]">
                    <p class="font-display text-lg font-bold leading-snug text-ink">«كل طلب يخرج من مطبخنا كأنه هدية لشخص نحبه.»</p>
                    <p class="mt-2 text-[10px] font-bold text-brand">أمل العبادي — المؤسسة</p>
                </div>
            </div>

            <div class="pt-6 lg:pt-0">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand">حكايتنا</p>
                <h2 class="mt-2 max-w-md font-display text-[1.9rem] font-bold leading-snug text-ink text-balance">من مطبخ صغير في {{ $store->city }}، إلى بيوتكم.</h2>
                <div class="mt-4 space-y-3 text-sm leading-7 text-muted">
                    @foreach (array_filter(explode("\n\n", (string) $store->description)) as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                </div>

                <div class="mt-7 grid grid-cols-3 gap-3">
                    <div class="rounded-2xl bg-surface p-4 ring-1 ring-line/70">
                        <p class="font-display text-2xl font-bold text-brand">{{ $productCount }}</p>
                        <p class="mt-1 text-[10px] text-muted">منتجاً في المنيو</p>
                    </div>
                    <div class="rounded-2xl bg-surface p-4 ring-1 ring-line/70">
                        <p class="font-display text-2xl font-bold text-brand">{{ $store->lead_time_hours }}</p>
                        <p class="mt-1 text-[10px] text-muted">ساعة تحضير</p>
                    </div>
                    <div class="rounded-2xl bg-surface p-4 ring-1 ring-line/70">
                        <p class="font-display text-2xl font-bold text-brand">{{ $store->delivery_enabled ? \App\Support\Money::format($store->delivery_fee_fils) : 'مجاني' }}</p>
                        <p class="mt-1 text-[10px] text-muted">{{ $store->delivery_enabled ? 'رسوم التوصيل' : 'الاستلام' }}</p>
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-2">
                    <a href="{{ route('storefront.menu', $store) }}" class="btn-primary">اطلبي من المنيو</a>
                    <a href="{{ $store->whatsappLink('مرحباً، حابة أستفسر عن طلب خاص.') }}" target="_blank" rel="noopener" class="btn-secondary">
                        <svg class="size-4 text-sage" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                        طلب خاص
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="mx-auto mt-16 max-w-6xl px-5 sm:px-8">
        <div class="rounded-[2.5rem] border border-line/80 bg-surface px-6 py-10 sm:px-10">
            <div class="mx-auto max-w-lg text-center">
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand">كيف تطلبين؟</p>
                <h2 class="mt-2 font-display text-2xl font-bold text-ink">ثلاث خطوات، وطلبك في الطريق</h2>
            </div>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                @foreach ([
                    ['١', 'اختاري من المنيو', 'تصفّحي التصنيفات، واختاري الحجم والإضافات اللي تحبيها.'],
                    ['٢', 'حددي الموعد', 'اختاري تاريخ ووقت الاستلام أو التوصيل حسب أقل مدة تحضير.'],
                    ['٣', 'أكدي الطلب', 'نستلم طلبك على واتساب، نؤكد التفاصيل، ونبدأ التحضير.'],
                ] as [$number, $title, $body])
                    <div class="relative rounded-3xl bg-canvas/60 p-5">
                        <span class="flex size-9 items-center justify-center rounded-full bg-brand font-display text-sm font-bold text-white">{{ $number }}</span>
                        <p class="mt-4 text-sm font-bold text-ink">{{ $title }}</p>
                        <p class="mt-1.5 text-[11px] leading-6 text-muted">{{ $body }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto mt-12 max-w-6xl px-5 sm:px-8">
        <div class="relative overflow-hidden rounded-[2.5rem] bg-gradient-to-l from-brand-soft via-gold-soft/70 to-surface p-7 sm:p-10">
            <div class="absolute -end-10 -top-16 size-48 rounded-full bg-brand/10 blur-2xl"></div>
            <div class="relative flex flex-col items-start justify-between gap-5 sm:flex-row sm:items-center">
                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand">عندك مناسبة قريبة؟</p>
                    <h2 class="mt-2 max-w-md font-display text-2xl font-bold leading-snug text-ink">خلّينا نجهّز لك طلباً يليق بالمناسبة.</h2>
                    <p class="mt-1.5 text-[11px] text-muted">كيك مناسبات، بوكسات ضيافة، أو تشكيلة هدايا — كلمينا ونرتّبها معك.</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    <a href="{{ $store->whatsappLink('مرحباً، عندي مناسبة قريبة وحابة أرتّب طلب خاص.') }}" target="_blank" rel="noopener" class="btn-primary">
                        تواصلي على واتساب
                    </a>
                    <a href="{{ route('storefront.menu', $store) }}" class="btn-secondary">تصفّحي المنيو</a>
                </div>
            </div>
        </div>
    </section>
@endsection