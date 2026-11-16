@props(['store', 'alt' => null])

@php
    $cover = $store->coverUrl();
    $alt = $alt ?? $store->name;
    $initial = mb_substr(trim((string) $store->name), 0, 1) ?: 'د';
@endphp

@if ($cover)
    <img src="{{ $cover }}" alt="{{ $alt }}" {{ $attributes->class(['object-cover']) }}>
@else
    {{-- Placeholder cover: shows the store's first letter until the owner
         uploads her own cover, so a new store never borrows another brand. --}}
    <div role="img" aria-label="{{ $alt }}"
         {{ $attributes->class(['grid place-items-center bg-gradient-to-br from-brand-dark via-brand to-[#d8a074]']) }}>
        <span aria-hidden="true" class="select-none font-display font-bold leading-none text-white/90"
              style="font-size: clamp(3.5rem, 15vmin, 9rem);">{{ $initial }}</span>
    </div>
@endif
