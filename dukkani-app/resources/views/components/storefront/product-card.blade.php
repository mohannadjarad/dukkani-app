@props(['product', 'store'])

<a href="{{ route('storefront.product', [$store, $product]) }}" class="group card card-hover flex flex-col overflow-hidden">
    <div class="relative aspect-[4/3] overflow-hidden bg-canvas">
        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy"
             class="size-full object-cover transition duration-500 group-hover:scale-[1.04]">

        <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-2 p-3">
            @if ($product->is_featured)
                <span class="inline-flex items-center gap-1 rounded-full bg-gold-soft/95 px-2.5 py-1 text-[9px] font-bold text-[#8a672c] shadow-sm backdrop-blur">
                    <span class="size-1.5 rounded-full bg-gold"></span>
                    الأكثر طلباً
                </span>
            @else
                <span></span>
            @endif

            @if ($product->category)
                <span class="rounded-full bg-white/90 px-2.5 py-1 text-[9px] font-bold text-ink-soft shadow-sm backdrop-blur">{{ $product->category->name }}</span>
            @endif
        </div>
    </div>

    <div class="flex flex-1 flex-col p-4">
        <div class="flex items-start justify-between gap-2">
            <h3 class="font-display text-lg font-bold leading-snug text-ink">{{ $product->name }}</h3>
            <span class="shrink-0 rounded-full bg-brand-soft px-2.5 py-1 text-[11px] font-bold text-brand-dark">{{ $product->priceFormatted() }}</span>
        </div>

        <p class="mt-1.5 line-clamp-2 text-[11px] leading-5 text-muted">{{ $product->description }}</p>

        <div class="mt-auto flex items-center justify-between gap-2 pt-4">
            <span class="flex items-center gap-1 text-[10px] text-muted/70">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                تحضير {{ $product->lead_time_hours ?: $store->lead_time_hours }} ساعة
            </span>
            <span class="flex items-center gap-1 text-[11px] font-bold text-brand transition group-hover:gap-2">
                اطلبي الآن
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 19.5 12m0 0-9-7.5m9 7.5H3"/></svg>
            </span>
        </div>
    </div>
</a>