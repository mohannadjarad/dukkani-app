@props(['size' => 'md', 'light' => false, 'showTagline' => true])

@php
    $mark = match ($size) {
        'sm' => 'size-9 text-base',
        'lg' => 'size-14 text-2xl',
        default => 'size-11 text-lg',
    };
    $title = match ($size) {
        'sm' => 'text-base',
        'lg' => 'text-2xl',
        default => 'text-lg',
    };
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <span class="flex {{ $mark }} shrink-0 items-center justify-center rounded-2xl {{ $light ? 'bg-white/15 text-white ring-1 ring-white/25' : 'bg-brand text-white shadow-[0_8px_18px_-10px_rgba(142,68,37,0.9)]' }} font-display font-bold">
        أ
    </span>
    <span class="flex min-w-0 flex-col leading-none">
        <span class="font-display {{ $title }} font-bold {{ $light ? 'text-white' : 'text-ink' }}">أمل بيكنز</span>
        @if ($showTagline)
            <span class="mt-1.5 text-[9px] font-bold uppercase tracking-[0.34em] {{ $light ? 'text-white/55' : 'text-muted/80' }}">Amal Bakes</span>
        @endif
    </span>
</span>
