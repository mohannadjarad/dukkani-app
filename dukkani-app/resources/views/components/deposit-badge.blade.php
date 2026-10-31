@props(['status'])

@php
    $palette = match ($status->color()) {
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-200/80',
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/80',
        'red' => 'bg-red-50 text-red-700 ring-red-200/80',
        default => 'bg-stone-100 text-stone-600 ring-stone-200/80',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge {$palette}"]) }}>
    <span class="size-1.5 rounded-full bg-current"></span>
    {{ $status->label() }}
</span>
