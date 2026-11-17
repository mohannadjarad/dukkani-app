@props(['store', 'size' => 'size-10', 'radius' => 'rounded-2xl', 'text' => 'text-lg', 'tone' => 'brand'])

@php
    $logo = $store->logoUrl();
    $initial = mb_substr(trim((string) $store->name), 0, 1) ?: 'د';
    $fallback = $tone === 'soft'
        ? 'bg-brand-soft text-brand-dark'
        : 'bg-brand text-white';
@endphp

@if ($logo)
    <img src="{{ $logo }}" alt="{{ $store->name }}"
         {{ $attributes->class(['shrink-0 object-cover', $size, $radius]) }}>
@else
    <span aria-hidden="true"
          {{ $attributes->class(['flex shrink-0 items-center justify-center font-display font-bold', $size, $radius, $text, $fallback]) }}>
        {{ $initial }}
    </span>
@endif
