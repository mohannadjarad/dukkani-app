@props(['paginator', 'label' => 'عنصر'])

@if ($paginator->hasPages())
    <div class="mt-5 flex flex-col items-center justify-between gap-3 sm:flex-row">
        <p class="text-[10px] text-muted">
            عرض {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} من {{ $paginator->total() }} {{ $label }}
        </p>
        <div class="flex items-center gap-2">
            @if ($paginator->onFirstPage())
                <span class="btn-secondary btn-sm cursor-not-allowed opacity-50">السابق</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn-secondary btn-sm">السابق</a>
            @endif
            <span class="text-[11px] font-bold text-ink-soft">{{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn-secondary btn-sm">التالي</a>
            @else
                <span class="btn-secondary btn-sm cursor-not-allowed opacity-50">التالي</span>
            @endif
        </div>
    </div>
@elseif ($paginator->total() > 0)
    <p class="mt-5 text-[10px] text-muted">{{ $paginator->total() }} {{ $label }}</p>
@endif