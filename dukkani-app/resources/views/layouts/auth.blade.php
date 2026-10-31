<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#f8f3ea">
    <meta name="robots" content="noindex">
    <title>@yield('title', 'تسجيل الدخول') — أمل بيكنز</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas">
    <main class="grid min-h-screen lg:grid-cols-[1.02fr_0.98fr]">
        <section class="flex flex-col px-6 py-7 sm:px-10 lg:px-16">
            <header class="flex items-center justify-between gap-4">
                <x-brand-mark size="sm" />
                <span class="demo-pill">
                    <span class="size-1.5 rounded-full bg-gold"></span>
                    عرض تجريبي
                </span>
            </header>

            <div class="mx-auto flex w-full max-w-[26rem] flex-1 flex-col justify-center py-12">
                @yield('content')
            </div>

            <footer class="flex flex-col gap-2 text-[11px] text-muted/80 sm:flex-row sm:items-center sm:justify-between">
                <span>© {{ now()->year }} أمل بيكنز — متجر تجريبي من دكّاني</span>
                <span>البيانات محلية وتجريبية بالكامل</span>
            </footer>
        </section>

        <aside class="relative hidden p-4 lg:block">
            <div class="relative h-full min-h-[calc(100vh-2rem)] overflow-hidden rounded-[2.5rem] bg-ink">
                <img src="{{ asset('images/amal-bakes/store-cover.webp') }}"
                     alt="داخل مخبز أمل بيكنز"
                     class="absolute inset-0 size-full object-cover opacity-90">
                <div class="absolute inset-0 bg-gradient-to-t from-[#1d110c]/95 via-[#1d110c]/35 to-[#1d110c]/10"></div>
                <div class="absolute inset-0 pattern-dots opacity-20"></div>

                <div class="relative flex h-full flex-col justify-between p-10 xl:p-14">
                    <div class="flex justify-end">
                        <span class="rounded-full border border-white/20 bg-white/10 px-4 py-2 text-[11px] font-bold text-white/80 backdrop-blur">
                            صُنع بحب في عمّان
                        </span>
                    </div>

                    <div class="max-w-md">
                        <p class="font-display text-4xl font-bold leading-[1.35] text-white xl:text-[2.75rem]">
                            «كل طلب يخرج من مطبخنا<br>كأنه هدية لشخص نحبه.»
                        </p>
                        <p class="mt-5 text-sm leading-7 text-white/65">
                            من كيكة العسل التي بدأت بها الحكاية، إلى بوكسات الضيافة التي تصل بيوتكم — كل تفصيل يُخبز ويُغلّف بعناية.
                        </p>

                        <div class="mt-8 flex items-center gap-4 border-t border-white/15 pt-6">
                            <span class="flex size-12 items-center justify-center rounded-full bg-white/10 font-display text-lg font-bold text-white ring-1 ring-white/20">أ</span>
                            <div>
                                <p class="text-sm font-bold text-white">أمل العبادي</p>
                                <p class="mt-0.5 text-[11px] text-white/55">المؤسسة وبائعة الحلويات</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-3">
                        <div class="rounded-2xl border border-white/12 bg-white/8 p-4 backdrop-blur-sm">
                            <p class="font-display text-2xl font-bold text-white">٣٢</p>
                            <p class="mt-1 text-[10px] text-white/55">طلباً هذا الشهر</p>
                        </div>
                        <div class="rounded-2xl border border-white/12 bg-white/8 p-4 backdrop-blur-sm">
                            <p class="font-display text-2xl font-bold text-white">١٤</p>
                            <p class="mt-1 text-[10px] text-white/55">عميلاً يثق بنا</p>
                        </div>
                        <div class="rounded-2xl border border-white/12 bg-white/8 p-4 backdrop-blur-sm">
                            <p class="font-display text-2xl font-bold text-white">٤٫٩</p>
                            <p class="mt-1 text-[10px] text-white/55">تقييم العملاء</p>
                        </div>
                    </div>
                </div>
            </div>
        </aside>
    </main>
</body>
</html>
