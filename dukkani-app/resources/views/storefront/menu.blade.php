@extends('layouts.storefront')

@section('title', 'المنيو الكامل')

@section('content')
    <section class="mx-auto max-w-6xl px-5 pt-8 sm:px-8 sm:pt-10">
        <nav class="flex items-center gap-1.5 text-[10px] font-medium text-muted/70">
            <a href="{{ route('storefront.home', $store) }}" class="transition hover:text-brand">الرئيسية</a>
            <svg class="size-3 text-muted/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
            <span class="text-ink-soft">المنيو</span>
        </nav>

        <div class="mt-4 flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand">منيو {{ $store->name }}</p>
                <h1 class="mt-2 font-display text-[2.1rem] font-bold leading-tight text-ink sm:text-[2.6rem]">كل ما نخبزه، بين يديك</h1>
                <p class="mt-2 max-w-xl text-sm leading-7 text-muted">
                    اختاري ما يناسب مناسبتك، وحددي الحجم والإضافات. كل طلب يُحضّر طازجاً بعد تأكيده — أقل مدة تحضير {{ $store->lead_time_hours }} ساعة.
                </p>
            </div>

            <span class="hidden items-center gap-2 rounded-full border border-line bg-surface px-4 py-2 text-[11px] font-bold text-muted sm:inline-flex">
                <span class="size-1.5 rounded-full bg-brand"></span>
                {{ $totalProducts }} منتجات متاحة
            </span>
        </div>

        <div class="mt-7 rounded-3xl border border-line/80 bg-surface p-4 sm:p-5">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="no-scrollbar -mx-1 flex gap-1.5 overflow-x-auto px-1 pb-1">
                    <a href="{{ route('storefront.menu', $store) }}"
                       class="shrink-0 rounded-full px-3.5 py-2 text-[11px] font-bold transition {{ $categoryId === 0 ? 'bg-brand text-white shadow-[0_8px_18px_-12px_rgba(142,68,37,0.9)]' : 'bg-canvas text-ink-soft hover:bg-brand-soft hover:text-brand-dark' }}">
                        الكل
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('storefront.menu', ['store' => $store, 'category' => $category->id]) }}"
                           class="shrink-0 rounded-full px-3.5 py-2 text-[11px] font-bold transition {{ $categoryId === $category->id ? 'bg-brand text-white shadow-[0_8px_18px_-12px_rgba(142,68,37,0.9)]' : 'bg-canvas text-ink-soft hover:bg-brand-soft hover:text-brand-dark' }}">
                            {{ $category->name }}
                            <span class="ms-1 text-[9px] {{ $categoryId === $category->id ? 'text-white/60' : 'text-muted/50' }}">{{ $category->products_count }}</span>
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('storefront.menu', $store) }}" class="relative w-full lg:w-64">
                    @if ($categoryId > 0)
                        <input type="hidden" name="category" value="{{ $categoryId }}">
                    @endif
                    <span class="pointer-events-none absolute inset-y-0 start-3.5 flex items-center text-muted/50">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    </span>
                    <input type="search" name="q" value="{{ $search }}" placeholder="ابحثي في المنيو…"
                           class="input py-2.5 ps-10 text-xs" aria-label="بحث في المنيو">
                </form>
            </div>
        </div>

        @if ($products->isNotEmpty())
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($products as $product)
                    <x-storefront.product-card :$product :$store />
                @endforeach
            </div>
        @else
            <div class="mt-8 flex flex-col items-center justify-center rounded-[2.5rem] border border-dashed border-line bg-surface/60 px-6 py-16 text-center">
                <span class="flex size-14 items-center justify-center rounded-full bg-canvas text-muted">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75-1.5.75a3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0 3.354 3.354 0 0 0-3 0 3.354 3.354 0 0 1-3 0L3 15.75m18-6.75v6.75a1.5 1.5 0 0 1-1.5 1.5H4.5A1.5 1.5 0 0 1 3 15.75V9m18 0a1.5 1.5 0 0 0-1.5-1.5H4.5A1.5 1.5 0 0 0 3 9"/></svg>
                </span>
                <p class="mt-4 font-display text-xl font-bold text-ink">لا توجد نتائج مطابقة</p>
                <p class="mt-1.5 max-w-sm text-xs leading-6 text-muted">
                    جرّبي كلمة بحث أخرى أو اختاري تصنيفاً مختلفاً. ونحن جاهزون لأي طلب خاص على واتساب.
                </p>
                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    <a href="{{ route('storefront.menu', $store) }}" class="btn-secondary btn-sm">عرض المنيو الكامل</a>
                    <a href="{{ $store->whatsappLink('مرحباً، حابة أستفسر عن منتج معين.') }}" target="_blank" rel="noopener" class="btn-primary btn-sm">اسألي على واتساب</a>
                </div>
            </div>
        @endif
    </section>

    <section class="mx-auto mt-12 max-w-6xl px-5 sm:px-8">
        <div class="flex flex-col items-start justify-between gap-4 rounded-[2.5rem] bg-gradient-to-l from-brand-soft via-gold-soft/60 to-surface p-6 sm:flex-row sm:items-center sm:p-8">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand">لم تجدي ما تبحثين عنه؟</p>
                <h2 class="mt-2 font-display text-xl font-bold text-ink sm:text-2xl">نصمّم لك طلباً خاصاً على ذوقك.</h2>
                <p class="mt-1.5 text-[11px] text-muted">كيك مناسبات، بوكس هدايا، أو كميات خاصة للشركات — كلمينا.</p>
            </div>
            <a href="{{ $store->whatsappLink('مرحباً، حابة أستفسر عن طلب خاص.') }}" target="_blank" rel="noopener" class="btn-primary shrink-0">تواصلي على واتساب</a>
        </div>
    </section>
@endsection