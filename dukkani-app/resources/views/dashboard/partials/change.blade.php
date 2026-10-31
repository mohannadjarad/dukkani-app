@if (round((float) $value, 1) === 0.0)
    <span class="inline-flex items-center gap-1 rounded-full bg-canvas px-2 py-1 text-[10px] font-bold text-muted">ثابت</span>
@else
    <span class="inline-flex items-center gap-1 rounded-full px-2 py-1 text-[10px] font-bold {{ $positive ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
        @if ($positive)
            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75 12 8.25l7.5 7.5"/></svg>
        @else
            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
        @endif
        {{ abs(round((float) $value, 1)) }}%
    </span>
@endif
