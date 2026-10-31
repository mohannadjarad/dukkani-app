@props(['status'])

@php
    $palette = match ($status->color()) {
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-200/80',
        'blue' => 'bg-blue-50 text-blue-700 ring-blue-200/80',
        'violet' => 'bg-violet-50 text-violet-700 ring-violet-200/80',
        'teal' => 'bg-teal-50 text-teal-700 ring-teal-200/80',
        'orange' => 'bg-orange-50 text-orange-700 ring-orange-200/80',
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-200/80',
        'red' => 'bg-red-50 text-red-700 ring-red-200/80',
        default => 'bg-stone-100 text-stone-600 ring-stone-200/80',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge {$palette}"]) }}>
    <span class="size-1.5 rounded-full bg-current"></span>
    {{ $status->label() }}
</span>
