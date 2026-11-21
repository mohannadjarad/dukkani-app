@extends('layouts.storefront')

@section('title', 'متابعة طلبي')

@section('content')
    <section class="mx-auto max-w-3xl px-5 pt-8 sm:px-8 sm:pt-10">
        <nav class="flex items-center gap-1.5 text-[10px] font-medium text-muted/70">
            <a href="{{ route('storefront.home', $store) }}" class="transition hover:text-brand">الرئيسية</a>
            <svg class="size-3 text-muted/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
            <span class="text-ink-soft">متابعة طلبي</span>
        </nav>

        <div class="relative mt-5 overflow-hidden rounded-[2.5rem] border border-sage/20 bg-gradient-to-l from-sage-soft/80 via-surface to-brand-soft/60 p-6 sm:p-8">
            <div class="absolute -end-10 -top-14 size-44 rounded-full bg-sage/10 blur-2xl"></div>

            <div class="relative flex items-start gap-4">
                <span class="flex size-14 shrink-0 items-center justify-center rounded-2xl bg-white/70 text-sage shadow-sm">
                    <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                </span>

                <div>
                    <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-sage">رجوع سريع</p>
                    <h1 class="mt-1.5 font-display text-[1.8rem] font-bold leading-tight text-ink sm:text-[2.2rem]">متابعة طلبي</h1>
                    <p class="mt-1.5 max-w-lg text-xs leading-6 text-muted">
                        ما بحاجة تحفظي رقم الطلب. أدخلي رقم الهاتف اللي طلبتِ فيه، وبنعرضلك طلباتك في {{ $store->name }}.
                    </p>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('storefront.track.find', $store) }}" class="card mt-5 p-5 sm:p-6">
            @csrf

            <label for="phone" class="label">رقم الهاتف</label>
            <div class="flex flex-col gap-3 sm:flex-row">
                <div class="input flex min-w-0 flex-1 items-center gap-2 focus-within:border-brand/60 focus-within:ring-4 focus-within:ring-brand/10"
                     dir="ltr">
                    <span class="shrink-0 text-xs font-bold text-muted/80" dir="ltr">+{{ $store->dialCode() }}</span>
                    <input id="phone" name="phone" type="tel" dir="ltr" required autocomplete="tel"
                           value="{{ old('phone') }}"
                           class="min-w-0 flex-1 border-0 bg-transparent p-0 text-sm text-ink placeholder:text-muted/55 focus:outline-none focus:ring-0"
                           placeholder="{{ \App\Support\Country::example($store->dialCode()) }}">
                </div>
                <button class="btn-primary shrink-0">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    عرض طلباتي
                </button>
            </div>
            @error('phone') <p class="field-error">{{ $message }}</p> @enderror

            <p class="mt-2.5 text-[10px] leading-5 text-muted/70">
                استخدمي نفس الرقم اللي أدخلتيه وقت الطلب (مع رمز الدولة +{{ $store->dialCode() }} أو بدونه). بنعرض طلبات هالمتجر بس.
            </p>
        </form>

        @if ($orders->isNotEmpty())
            <div class="mt-6">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-sm font-bold text-ink">طلباتك بهذا الرقم</h2>
                    <a href="{{ route('storefront.track.lookup', $store) }}" class="text-[10px] font-bold text-brand transition hover:text-brand-dark">بحث جديد</a>
                </div>

                <div class="mt-3 space-y-2.5">
                    @foreach ($orders as $order)
                        <a href="{{ route('track.show', $order->public_token) }}"
                           class="card flex items-center justify-between gap-3 p-4 transition hover:border-brand/30">
                            <div class="min-w-0">
                                <p class="font-mono text-[11px] font-bold text-ink" dir="ltr">{{ $order->number }}</p>
                                <p class="mt-1 text-[10px] text-muted">
                                    {{ $order->scheduled_at->translatedFormat('j M Y، g:i A') }}
                                    • {{ $order->totalFormatted() }}
                                </p>
                            </div>
                            <div class="flex shrink-0 items-center gap-2">
                                <x-status-badge :status="$order->status" />
                                <svg class="size-4 text-muted/50" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <div class="mt-6 rounded-2xl border border-line/70 bg-surface px-4 py-3 text-[10px] leading-5 text-muted">
            <span class="font-bold text-ink">ملاحظة:</span> هالصفحة بترجعك لطلبك بأي وقت من نفس الرقم.
            إذا غيّرتِ رقم هاتفك أو ما طلعلك الطلب، راسلي المتجر على واتساب مع اسمك وموعد الطلب.
        </div>
    </section>
@endsection