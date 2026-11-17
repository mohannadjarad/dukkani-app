@php
    $isTrial = $usage['trial'] ?? false;
    $atLimit = $usage['at_limit'] ?? false;
    $nearLimit = $usage['near_limit'] ?? false;
    $limit = $usage['limit'];
    $used = $usage['used'];
    $remaining = $usage['remaining'];
@endphp

@if ($isTrial)
    <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-gold/30 bg-gold-soft/70 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <span class="text-lg leading-none">🎁</span>
            <div>
                <p class="text-xs font-bold text-[#7a5a22]">
                    مبروك! متجرك على Starter مجاناً — باقي {{ $usage['trial_days'] }} {{ $usage['trial_days'] === 1 ? 'يوم' : 'أيام' }}
                </p>
                <p class="mt-0.5 text-[10px] leading-5 text-[#8a672c]">طلبات بلا حدود، وحتى 12 منتجاً. بعدها بيرجع تلقائياً للخطة المجانية (15 طلب و12 منتج).</p>
            </div>
        </div>
        @if ($upgradeLink)
            <a href="{{ $upgradeLink }}" target="_blank" rel="noopener" class="btn-secondary btn-sm shrink-0">استفسري عن الترقية</a>
        @endif
    </div>
@elseif ($atLimit)
    <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-danger/25 bg-danger-soft px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-danger/10 text-danger">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </span>
            <div>
                <p class="text-xs font-bold text-danger">وصلتِ {{ $used }}/{{ $limit }} — رقّي الخطة لاستقبال طلبات جديدة</p>
                <p class="mt-0.5 text-[10px] leading-5 text-danger/80">الطلبات الجديدة محجوبة حالياً وتظهر للعميل رسالة بالتواصل عبر واتساب. الطلبات الحالية ما بتتأثر.</p>
            </div>
        </div>
        @if ($upgradeLink)
            <a href="{{ $upgradeLink }}" target="_blank" rel="noopener" class="btn-primary btn-sm shrink-0">واتساب الدعم</a>
        @endif
    </div>
@elseif ($nearLimit)
    <div class="mb-4 flex flex-col gap-3 rounded-2xl border border-gold/30 bg-gold-soft/60 px-4 py-3.5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-3">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-gold/15 text-[#a47b32]">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </span>
            <div>
                <p class="text-xs font-bold text-[#7a5a22]">تنبيه: استعملتِ {{ $used }} من {{ $limit }} طلباً هذا الشهر</p>
                <p class="mt-0.5 text-[10px] leading-5 text-[#8a672c]">باقي {{ $remaining }} {{ $remaining === 1 ? 'طلب' : 'طلبات' }} على الحد. رقّي الخطة قبل ما يوقف استقبال الطلبات.</p>
            </div>
        </div>
        @if ($upgradeLink)
            <a href="{{ $upgradeLink }}" target="_blank" rel="noopener" class="btn-secondary btn-sm shrink-0">رقّي الخطة</a>
        @endif
    </div>
@endif