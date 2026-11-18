{{--
    Product image with a safety net.

    - A stored image that already failed a check renders the neutral placeholder
      straight away (no dead request to a 404).
    - Anything that breaks later collapses to the same placeholder on the spot,
      then beacons the fact back to the dashboard so the owner reads
      «في مشكلة في صورة المنتج …» instead of hearing it from a customer.
--}}
@props([
    'product',
    'size' => 'thumb',
    'class' => 'size-full object-cover',
    'loading' => 'lazy',
    'alt' => null,
])

@php
    $src = $size === 'large' ? $product->displayImageUrl() : $product->displayThumbnailUrl();
    $store = request()->route('store');
    $reportable = $store && $product->hasImage() && ! $product->imageIsBroken();
@endphp

<img src="{{ $src }}"
     alt="{{ $alt ?? $product->name }}"
     @if ($loading) loading="{{ $loading }}" @endif
     class="{{ $class }}"
     data-fallback="{{ $product::placeholderUrl() }}"
     @if ($reportable) data-image-report="{{ URL::temporarySignedRoute('storefront.image-problem', now()->addDays(7), ['store' => $store, 'product' => $product]) }}" @endif
     onerror="this.onerror=null;var f=this.dataset.fallback;if(f){this.src=f;}this.classList.add('opacity-60');var r=this.dataset.imageReport;if(r){var t=document.querySelector('meta[name=csrf-token]');fetch(r,{method:'POST',keepalive:true,headers:t?{'X-CSRF-TOKEN':t.content}:{}}).catch(function(){});}">