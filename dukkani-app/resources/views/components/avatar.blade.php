@props(['name' => '', 'size' => 'md', 'tone' => 'brand'])

@php
    $parts = preg_split('/\s+/', trim($name));
    $initials = mb_substr($parts[0] ?? '', 0, 1).mb_substr($parts[1] ?? '', 0, 1);
    $dimensions = match ($size) {
        'sm' => 'size-8 text-[10px]',
        'lg' => 'size-12 text-sm',
        default => 'size-10 text-xs',
    };
    $tones = match ($tone) {
        'sage' => 'bg-sage-soft text-sage',
        'gold' => 'bg-gold-soft text-[#8a672c]',
        'stone' => 'bg-canvas text-muted',
        default => 'bg-brand-soft text-brand-dark',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex {$dimensions} shrink-0 items-center justify-center rounded-full {$tones} font-bold"]) }}>
    {{ $initials }}
</span>
