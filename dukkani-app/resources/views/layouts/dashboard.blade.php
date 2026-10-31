<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f8f3ea">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'لوحة التحكم') — أمل بيكنز</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="{ sidebar: false }" class="min-h-screen bg-canvas">
    @php
        $store = auth()->user()->store;
        $navItems = [
            ['label' => 'لوحة التحكم', 'route' => 'dashboard', 'active' => request()->routeIs('dashboard'), 'badge' => null, 'icon' => '<path d="M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h6v6h-6z"/>'],
            ['label' => 'الطلبات', 'route' => 'orders.index', 'active' => request()->routeIs('orders.*'), 'badge' => ($newOrders = $store?->orders()->where('status', 'new')->count()) ? $newOrders : null, 'icon' => '<path d="M8 3h8a2 2 0 0 1 2 2v16l-3-2-3 2-3-2-3 2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6M9 12h6M9 16h3"/>'],
            ['label' => 'المنتجات', 'route' => 'products.index', 'active' => request()->routeIs('products.*'), 'badge' => null, 'icon' => '<path d="m21 8-9-5-9 5v8l9 5 9-5V8Z"/><path d="m3.3 8.3 8.7 4.9 8.7-4.9M12 21v-7.8"/>'],
            ['label' => 'العملاء', 'route' => 'customers.index', 'active' => request()->routeIs('customers.*'), 'badge' => null, 'icon' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>'],
            ['label' => 'التحليلات', 'route' => 'analytics.index', 'active' => request()->routeIs('analytics.*'), 'badge' => null, 'icon' => '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>'],
            ['label' => 'الإعدادات', 'route' => 'settings.index', 'active' => request()->routeIs('settings.*'), 'badge' => null, 'icon' => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.87l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.7 1.7 0 0 0-1.87-.34 1.7 1.7 0 0 0-1 1.55V21a2 2 0 1 1-4 0v-.09a1.7 1.7 0 0 0-1-1.55 1.7 1.7 0 0 0-1.87.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-1.55-1H3a2 2 0 1 1 0-4h.09a1.7 1.7 0 0 0 1.55-1 1.7 1.7 0 0 0-.34-1.87l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6h.09a1.7 1.7 0 0 0 1-1.55V3a2 2 0 1 1 4 0v.09a1.7 1.7 0 0 0 1 1.55 1.7 1.7 0 0 0 1.87-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9v.09a1.7 1.7 0 0 0 1.55 1H21a2 2 0 1 1 0 4h-.09a1.7 1.7 0 0 0-1.51 1Z"/>'],
        ];
    @endphp

    <div x-show="sidebar" x-transition.opacity class="fixed inset-0 z-40 bg-ink/40 backdrop-blur-sm lg:hidden" @click="sidebar = false" x-cloak></div>

    <div class="flex min-h-screen">
        <aside
            class="fixed inset-y-0 start-0 z-50 flex w-[18rem] shrink-0 flex-col border-e border-line/80 bg-surface px-4 py-5 transition-transform duration-300 lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
            :class="sidebar ? 'translate-x-0' : 'translate-x-full lg:translate-x-0'"
        >
            <div class="flex items-center justify-between px-2">
                <x-brand-mark size="sm" />
                <button class="icon-btn lg:hidden" @click="sidebar = false" aria-label="إغلاق القائمة">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-6 rounded-2xl border border-line/80 bg-canvas/70 p-3">
                <div class="flex items-center gap-3">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-brand-soft font-display text-lg font-bold text-brand-dark">أ</span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-[13px] font-bold text-ink">{{ $store?->name ?? 'أمل بيكنز' }}</p>
                        <p class="mt-0.5 flex items-center gap-1.5 text-[10px] text-muted">
                            <span class="size-1.5 rounded-full bg-emerald-500"></span>
                            متجر نشط
                        </p>
                    </div>
                    <svg class="size-4 shrink-0 text-muted/60" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m8 9 4-4 4 4M8 15l4 4 4-4"/></svg>
                </div>
            </div>

            <nav class="mt-7 flex-1 space-y-1 overflow-y-auto no-scrollbar">
                <p class="px-3 pb-2 text-[10px] font-bold uppercase tracking-[0.18em] text-muted/55">إدارة المتجر</p>

                @foreach ($navItems as $item)
                    <a href="{{ route($item['route']) }}" class="sidebar-link {{ $item['active'] ? 'sidebar-link-active' : '' }}">
                        <svg class="size-[19px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">{!! $item['icon'] !!}</svg>
                        <span class="flex-1">{{ $item['label'] }}</span>
                        @if (! empty($item['badge']))
                            <span class="inline-flex min-w-5 items-center justify-center rounded-full px-1.5 py-0.5 text-[9px] font-bold {{ $item['active'] ? 'bg-white text-brand-dark' : 'bg-brand text-white' }}">{{ $item['badge'] }}</span>
                        @endif
                        @if ($item['active'])
                            <span class="size-1.5 rounded-full bg-white/70"></span>
                        @endif
                    </a>
                @endforeach

                <div class="my-3 border-t border-line/70"></div>

                <a href="{{ route('storefront.home', $store) }}" target="_blank" class="sidebar-link">
                    <svg class="size-[19px] shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path d="M3 9h18M9 21V9M5 3h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z"/></svg>
                    <span class="flex-1">عرض المتجر</span>
                    <svg class="size-3.5 shrink-0 text-muted/50" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg>
                </a>
            </nav>

            <div class="mt-4 rounded-2xl bg-gradient-to-bl from-gold-soft/80 to-brand-soft/50 p-3.5">
                <div class="flex items-center gap-2 text-[10px] font-bold text-[#8a672c]">
                    <span class="size-1.5 rounded-full bg-gold"></span>
                    عرض تجريبي
                </div>
                <p class="mt-1.5 text-[10px] leading-5 text-muted">كل البيانات هنا وهمية، ولا يتم إرسال رسائل أو مدفوعات حقيقية.</p>
            </div>

            <div class="mt-3 flex items-center gap-3 rounded-2xl border border-line/70 p-2.5" x-data="{ open: false }" @click.outside="open = false">
                <x-avatar :name="auth()->user()->name" size="sm" />
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs font-bold text-ink">{{ auth()->user()->name }}</p>
                    <p class="truncate text-[10px] text-muted" dir="ltr">{{ auth()->user()->email }}</p>
                </div>
                <div class="relative">
                    <button class="icon-btn !size-8" @click="open = !open" aria-label="خيارات الحساب">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 12.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5ZM12 18.75a.75.75 0 1 1 0-1.5.75.75 0 0 1 0 1.5Z"/></svg>
                    </button>
                    <div x-show="open" x-transition x-cloak class="absolute bottom-full end-0 z-50 mb-2 w-48 rounded-2xl border border-line bg-surface p-1.5 shadow-[var(--shadow-lift)]">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2.5 text-xs font-bold text-danger transition hover:bg-danger-soft">
                                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l3 3m0 0-3 3m3-3H3"/></svg>
                                تسجيل الخروج
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-30 border-b border-line/70 bg-canvas/85 backdrop-blur-xl">
                <div class="flex h-[4.4rem] items-center gap-3 px-5 sm:px-8">
                    <button class="icon-btn lg:hidden" @click="sidebar = true" aria-label="فتح القائمة">
                        <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                    </button>

                    <div class="min-w-0 flex-1">
                        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-muted/60">لوحة التحكم</p>
                        <h2 class="truncate text-sm font-bold text-ink">@yield('page-title', 'نظرة عامة')</h2>
                    </div>

                    <a href="{{ route('storefront.home', $store) }}" target="_blank" class="btn-secondary btn-sm hidden md:inline-flex">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                        عرض المتجر
                    </a>

                    <span class="demo-pill hidden sm:inline-flex">
                        <span class="size-1.5 rounded-full bg-gold"></span>
                        متجر تجريبي
                    </span>

                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button class="icon-btn relative" @click="open = !open" aria-label="الإشعارات">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"/></svg>
                            @if (($unreadCount = $store?->notifications()->unread()->count() ?? 0) > 0)
                                <span class="absolute end-1.5 top-1.5 flex size-4 items-center justify-center rounded-full bg-brand text-[8px] font-bold text-white ring-2 ring-canvas">{{ $unreadCount }}</span>
                            @endif
                        </button>

                        <div x-show="open" x-transition.origin.top.end x-cloak class="absolute end-0 top-full z-50 mt-2 w-[21rem] overflow-hidden rounded-3xl border border-line bg-surface shadow-[var(--shadow-lift)]">
                            <div class="flex items-center justify-between border-b border-line/70 px-4 py-3">
                                <p class="text-xs font-bold text-ink">الإشعارات</p>
                                @if ($unreadCount > 0)
                                    <form method="POST" action="{{ route('notifications.read') }}">
                                        @csrf
                                        <button class="text-[10px] font-bold text-brand transition hover:text-brand-dark">تعليم الكل كمقروء</button>
                                    </form>
                                @else
                                    <span class="text-[10px] text-muted">لا جديد</span>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse (($store?->notifications()->latest()->take(5)->get() ?? collect()) as $notification)
                                    <form method="POST" action="{{ route('notifications.open', $notification) }}" class="block">
                                        @csrf
                                        <button type="submit" class="flex w-full gap-3 border-b border-line/50 px-4 py-3 text-start transition last:border-0 hover:bg-canvas/60 {{ $notification->read_at ? 'opacity-60' : '' }}">
                                            <span class="mt-1 size-2 shrink-0 rounded-full {{ $notification->read_at ? 'bg-line' : 'bg-brand' }}"></span>
                                            <div class="min-w-0">
                                                <p class="text-[11px] font-bold text-ink">{{ $notification->title }}</p>
                                                <p class="mt-0.5 truncate text-[10px] text-muted">{{ $notification->body }}</p>
                                                <p class="mt-1 text-[9px] text-muted/60">{{ $notification->created_at->diffForHumans() }}</p>
                                            </div>
                                        </button>
                                    </form>
                                @empty
                                    <p class="px-4 py-8 text-center text-[11px] text-muted">لا توجد إشعارات بعد.</p>
                                @endforelse
                            </div>
                            <a href="{{ route('orders.index') }}" class="block border-t border-line/70 bg-canvas/50 px-4 py-2.5 text-center text-[10px] font-bold text-muted/80 transition hover:text-brand">عرض كل الطلبات</a>
                        </div>
                    </div>

                    <div class="hidden h-6 w-px bg-line sm:block"></div>
                    <span class="hidden sm:inline-flex">
                        <x-avatar :name="auth()->user()->name" size="sm" />
                    </span>
                </div>
            </header>

            <main class="mx-auto w-full max-w-[1440px] flex-1 px-5 py-7 sm:px-8 lg:py-9">
                @yield('content')

                <footer class="mt-10 flex flex-col gap-2 border-t border-line/60 pt-5 text-[10px] text-muted/60 sm:flex-row sm:items-center sm:justify-between">
                    <span>أمل بيكنز — عرض تجريبي من دكّاني</span>
                    <span>بيانات وهمية • لا مدفوعات أو رسائل حقيقية</span>
                </footer>
            </main>
        </div>
    </div>

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
