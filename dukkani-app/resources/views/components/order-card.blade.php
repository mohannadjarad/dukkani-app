@props(['order'])

@php
    $next = collect($order->status->nextStatuses())
        ->reject(fn ($status) => $status === \App\Enums\OrderStatus::Cancelled)
        ->first();
@endphp

<div class="relative rounded-2xl border border-line/70 bg-white p-3.5 shadow-[0_1px_2px_rgba(42,28,21,0.03)] transition hover:border-brand/30 hover:shadow-[var(--shadow-soft)]">
    <a href="{{ route('orders.show', $order) }}" class="absolute inset-0 rounded-2xl" aria-label="فتح الطلب {{ $order->number }}"></a>

    <div class="pointer-events-none relative z-10">
        <div class="flex items-center justify-between gap-2">
            <span class="font-mono text-[10px] font-bold text-muted" dir="ltr">{{ $order->number }}</span>
            @if ($order->isLate())
                <span class="rounded-full bg-danger-soft px-2 py-0.5 text-[8px] font-bold text-danger">متأخر</span>
            @elseif ($order->deposit_status === \App\Enums\DepositStatus::Pending)
                <span class="rounded-full bg-gold-soft px-2 py-0.5 text-[8px] font-bold text-[#8a672c]">عربون معلّق</span>
            @endif
        </div>

        <div class="mt-2 flex items-center gap-2.5">
            <x-avatar :name="$order->customer->name" size="sm" tone="stone" />
            <div class="min-w-0 flex-1">
                <p class="truncate text-[11px] font-bold text-ink">{{ $order->customer->name }}</p>
                <p class="truncate text-[9px] text-muted/70" dir="ltr">{{ $order->customer->phone }}</p>
            </div>
        </div>

        <p class="mt-2 truncate text-[10px] text-muted">{{ $order->itemsSummary() }}</p>
    </div>

    <div class="mt-3 flex items-center justify-between gap-2 border-t border-line/60 pt-2.5">
        <div class="pointer-events-none relative z-10">
            <p class="text-[10px] font-bold text-ink">{{ $order->totalFormatted() }}</p>
            <p class="mt-0.5 text-[9px] text-muted/70">{{ $order->scheduled_at->translatedFormat('j M، g:i A') }}</p>
        </div>

        @if ($next)
            <form method="POST" action="{{ route('orders.status', $order) }}" class="relative z-20">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $next->value }}">
                <button class="inline-flex items-center gap-1 rounded-full bg-brand-soft px-2.5 py-1 text-[9px] font-bold text-brand-dark transition hover:bg-brand hover:text-white" title="نقل إلى {{ $next->label() }}">
                    {{ $next->label() }}
                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
                </button>
            </form>
        @else
            <span class="relative z-10 text-[9px] text-muted/60">{{ $order->fulfillment_type->label() }}</span>
        @endif
    </div>
</div>
