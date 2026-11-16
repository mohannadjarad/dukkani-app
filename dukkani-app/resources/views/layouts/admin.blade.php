<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f8f3ea">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'لوحة الإدارة') — {{ $brandName }}</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas">
    <header class="sticky top-0 z-30 border-b border-line/70 bg-canvas/85 backdrop-blur-xl">
        <div class="mx-auto flex h-[4.4rem] max-w-[1200px] items-center gap-3 px-5 sm:px-8">
            <x-brand-mark size="sm" />

            <div class="min-w-0 flex-1 ps-2">
                <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-muted/60">لوحة الإدارة</p>
                <h2 class="truncate text-sm font-bold text-ink">@yield('page-title', 'المتاجر')</h2>
            </div>

            <span class="hidden items-center gap-2 rounded-full border border-line bg-surface px-3.5 py-1.5 text-[10px] font-bold text-muted sm:inline-flex">
                <span class="size-1.5 rounded-full bg-emerald-500"></span>
                {{ auth()->user()->email }}
            </span>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn-secondary btn-sm">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H3"/></svg>
                    خروج
                </button>
            </form>
        </div>
    </header>

    <main class="mx-auto w-full max-w-[1200px] flex-1 px-5 py-7 sm:px-8">
        @yield('content')

        <footer class="mt-10 border-t border-line/60 pt-5 text-[10px] text-muted/60">
            {{ $brandName }} — لوحة إدارة المنصة
        </footer>
    </main>

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
