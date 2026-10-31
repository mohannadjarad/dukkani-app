<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f8f3ea">
    <meta name="description" content="{{ $store->tagline ?? $store->name }}">
    <title>@yield('title', $store->name) — {{ $store->name }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas" x-data="{ mobile: false }">
    <div class="bg-ink text-white/70">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-5 py-2 text-[10px] sm:px-8">
            <p class="flex min-w-0 items-center gap-2">
                <span class="size-1.5 shrink-0 rounded-full bg-gold"></span>
                <span class="truncate">{{ $store->tagline }}</span>
            </p>
            <p class="hidden shrink-0 items-center gap-1.5 sm:flex">
                <svg class="size-3.5 text-white/45" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                @if ($store->delivery_enabled)
                    توصيل داخل {{ $store->city }} — {{ \App\Support\Money::format($store->delivery_fee_fils) }}
                @else
                    استلام من المتجر
                @endif
            </p>
        </div>
    </div>

    <header class="sticky top-0 z-40 border-b border-line/70 bg-canvas/90 backdrop-blur-xl">
        <div class="mx-auto flex h-[4.5rem] max-w-6xl items-center gap-4 px-5 sm:px-8">
            <a href="{{ route('storefront.home', $store) }}" class="flex shrink-0 items-center gap-2.5">
                <span class="flex size-10 items-center justify-center rounded-2xl bg-brand font-display text-lg font-bold text-white shadow-[0_8px_18px_-10px_rgba(142,68,37,0.9)]">{{ mb_substr($store->name, 0, 1) }}</span>
                <span class="hidden flex-col leading-none sm:flex">
                    <span class="font-display text-lg font-bold text-ink">{{ $store->name }}</span>
                    <span class="mt-1 text-[8px] font-bold uppercase tracking-[0.32em] text-muted/75">Amal Bakes</span>
                </span>
            </a>

            <nav class="hidden items-center gap-0.5 lg:flex">
                <a href="{{ route('storefront.home', $store) }}" class="rounded-full px-3.5 py-2 text-[13px] font-bold transition {{ request()->routeIs('storefront.home') ? 'bg-brand-soft text-brand-dark' : 'text-ink-soft hover:bg-white hover:text-ink' }}">الرئيسية</a>
                <a href="{{ route('storefront.menu', $store) }}" class="rounded-full px-3.5 py-2 text-[13px] font-bold transition {{ request()->routeIs('storefront.menu', 'storefront.product') ? 'bg-brand-soft text-brand-dark' : 'text-ink-soft hover:bg-white hover:text-ink' }}">المنيو</a>
                <a href="{{ route('storefront.home', $store) }}#about" class="rounded-full px-3.5 py-2 text-[13px] font-bold text-ink-soft transition hover:bg-white hover:text-ink">عن المتجر</a>
                <a href="{{ route('storefront.home', $store) }}#contact" class="rounded-full px-3.5 py-2 text-[13px] font-bold text-ink-soft transition hover:bg-white hover:text-ink">تواصلي معنا</a>
            </nav>

            <div class="ms-auto flex items-center gap-1.5 sm:gap-2">
                <a href="{{ route('storefront.cart', $store) }}" class="icon-btn relative" aria-label="سلة الطلب">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z"/></svg>
                    @if ($cartCount > 0)
                        <span class="absolute -top-0.5 -end-0.5 flex min-w-5 items-center justify-center rounded-full bg-brand px-1.5 py-0.5 text-[9px] font-bold leading-none text-white ring-2 ring-canvas">{{ $cartCount }}</span>
                    @endif
                </a>

                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn-secondary btn-sm hidden sm:inline-flex">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z"/></svg>
                    {{ auth()->check() ? 'لوحة التحكم' : 'دخول البائعة' }}
                </a>

                <button class="icon-btn lg:hidden" @click="mobile = !mobile" :aria-expanded="mobile" aria-label="القائمة">
                    <svg x-show="!mobile" class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    <svg x-show="mobile" x-cloak class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <div x-show="mobile" x-transition.origin.top x-cloak class="border-t border-line/70 bg-surface lg:hidden">
            <nav class="mx-auto flex max-w-6xl flex-col gap-1 px-5 py-4 sm:px-8">
                <a href="{{ route('storefront.home', $store) }}" class="sidebar-link">الرئيسية</a>
                <a href="{{ route('storefront.menu', $store) }}" class="sidebar-link">المنيو</a>
                <a href="{{ route('storefront.home', $store) }}#about" class="sidebar-link">عن المتجر</a>
                <a href="{{ route('storefront.home', $store) }}#contact" class="sidebar-link">تواصلي معنا</a>
                <div class="my-1 border-t border-line/70"></div>
                <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="btn-secondary mt-1 w-full">
                    {{ auth()->check() ? 'لوحة التحكم' : 'دخول البائعة' }}
                </a>
            </nav>
        </div>
    </header>

    <main>
        @yield('content')
    </main>

    <footer class="mt-16 border-t border-line/70 bg-surface">
        <div class="mx-auto max-w-6xl px-5 py-12 sm:px-8">
            <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
                <div>
                    <div class="flex items-center gap-2.5">
                        <span class="flex size-10 items-center justify-center rounded-2xl bg-brand font-display text-lg font-bold text-white">{{ mb_substr($store->name, 0, 1) }}</span>
                        <div class="leading-none">
                            <p class="font-display text-lg font-bold text-ink">{{ $store->name }}</p>
                            <p class="mt-1 text-[8px] font-bold uppercase tracking-[0.3em] text-muted/70">Amal Bakes</p>
                        </div>
                    </div>
                    <p class="mt-4 max-w-xs text-xs leading-6 text-muted">{{ $store->tagline }}</p>
                    <span class="demo-pill mt-4">
                        <span class="size-1.5 rounded-full bg-gold"></span>
                        متجر تجريبي
                    </span>
                </div>

                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-muted/60">روابط سريعة</p>
                    <div class="mt-4 flex flex-col gap-2.5 text-xs text-ink-soft">
                        <a href="{{ route('storefront.home', $store) }}" class="transition hover:text-brand">الرئيسية</a>
                        <a href="{{ route('storefront.menu', $store) }}" class="transition hover:text-brand">المنيو الكامل</a>
                        <a href="{{ route('storefront.cart', $store) }}" class="transition hover:text-brand">سلة الطلب</a>
                        <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="transition hover:text-brand">{{ auth()->check() ? 'لوحة التحكم' : 'دخول البائعة' }}</a>
                    </div>
                </div>

                <div>
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-muted/60">ساعات العمل</p>
                    <div class="mt-4 space-y-1.5 text-[11px]">
                        @foreach ($store->hours as $hour)
                            <div class="flex items-center justify-between gap-3 {{ $hour->day_of_week === (int) now()->dayOfWeek ? 'font-bold text-ink' : 'text-muted' }}">
                                <span class="flex items-center gap-1.5">
                                    @if ($hour->day_of_week === (int) now()->dayOfWeek)
                                        <span class="size-1.5 rounded-full bg-brand"></span>
                                    @endif
                                    {{ $hour->dayName() }}
                                </span>
                                <span dir="ltr">{{ $hour->label() }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="contact">
                    <p class="text-[11px] font-bold uppercase tracking-[0.16em] text-muted/60">تواصلي معنا</p>
                    <div class="mt-4 space-y-3 text-xs text-ink-soft">
                        <a href="tel:{{ preg_replace('/\D+/', '', $store->phone) }}" class="flex items-center gap-2.5 transition hover:text-brand">
                            <span class="flex size-8 items-center justify-center rounded-xl bg-canvas text-muted"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg></span>
                            <span dir="ltr">{{ $store->phone }}</span>
                        </a>
                        <a href="{{ $store->whatsappLink('مرحباً، عندي استفسار عن منتجاتكم.') }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 transition hover:text-brand">
                            <span class="flex size-8 items-center justify-center rounded-xl bg-sage-soft text-sage"><svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg></span>
                            واتساب
                        </a>
                        @if ($store->instagram)
                            <a href="https://instagram.com/{{ ltrim($store->instagram, '@') }}" target="_blank" rel="noopener" class="flex items-center gap-2.5 transition hover:text-brand">
                                <span class="flex size-8 items-center justify-center rounded-xl bg-gold-soft text-[#a47b32]"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.2" cy="6.8" r="1" fill="currentColor" stroke="none"/></svg></span>
                                <span dir="ltr">{{ '@'.ltrim($store->instagram, '@') }}</span>
                            </a>
                        @endif
                        <a href="{{ $store->mapsUrl() }}" target="_blank" rel="noopener" class="flex items-start gap-2.5 transition hover:text-brand">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-xl bg-canvas text-muted"><svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z"/></svg></span>
                            <span class="leading-6">{{ $store->address }}<br><span class="text-muted/70">{{ $store->city }}</span></span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-10 flex flex-col gap-2 border-t border-line/60 pt-5 text-[10px] text-muted/60 sm:flex-row sm:items-center sm:justify-between">
                <span>© {{ now()->year }} {{ $store->name }} — صُنع بحب في {{ $store->city }}</span>
                <span>عرض تجريبي من دكّاني • بيانات وهمية ولا مدفوعات أو رسائل حقيقية</span>
            </div>
        </div>
    </footer>

    @if (session('status'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5500)" x-transition
             class="fixed bottom-6 start-6 z-[60] flex max-w-sm items-start gap-3 rounded-2xl border border-sage/20 bg-white px-4 py-3.5 shadow-[var(--shadow-lift)]">
            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-sage-soft text-sage">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            </span>
            <p class="pt-1 text-xs font-medium leading-5 text-ink-soft">{{ session('status') }}</p>
            <button @click="show = false" class="ms-auto text-muted hover:text-ink" aria-label="إغلاق">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    @stack('scripts')
</body>
</html>