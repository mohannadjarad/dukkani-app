@extends('layouts.auth')

@section('title', 'تسجيل الدخول')

@section('content')
    <div class="mb-8">
        <span class="inline-flex items-center gap-2 rounded-full bg-brand-soft px-3.5 py-1.5 text-[11px] font-bold text-brand-dark">
            <span class="size-1.5 rounded-full bg-brand"></span>
            لوحة تحكم البائعة
        </span>
        <h1 class="mt-5 font-display text-[2.6rem] font-bold leading-tight text-ink">أهلاً بعودتك، أمل</h1>
        <p class="mt-3 text-sm leading-7 text-muted">
            سجّلي الدخول لمتابعة طلبات اليوم، إدارة المنتجات، ومعرفة ما يحدث في متجرك.
        </p>
    </div>

    @if (config('app.demo_mode'))
        <form method="POST" action="{{ route('demo-login') }}" class="mb-7">
            @csrf
            <div class="relative overflow-hidden rounded-3xl border border-brand/20 bg-gradient-to-bl from-brand-soft via-surface to-gold-soft/60 p-5">
                <div class="absolute -left-8 -top-8 size-28 rounded-full bg-brand/10 blur-2xl"></div>
                <div class="relative">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold text-ink">جرّبي العرض التجريبي فوراً</p>
                            <p class="mt-1 text-xs leading-6 text-muted">دخول بنقرة واحدة بحساب جاهز ومليء ببيانات واقعية.</p>
                        </div>
                        <span class="flex size-10 shrink-0 items-center justify-center rounded-full bg-white/70 text-brand shadow-sm">
                            <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3"/></svg>
                        </span>
                    </div>
                    <button type="submit" class="btn-primary mt-4 w-full">
                        دخول سريع إلى لوحة التحكم
                    </button>
                    <p class="mt-2.5 text-center text-[10px] text-muted/80">لن يتم إرسال أي رسائل واتساب أو بريد حقيقية.</p>
                </div>
            </div>
        </form>
    @endif

    <div class="mb-6 flex items-center gap-3">
        <span class="h-px flex-1 bg-line"></span>
        <span class="text-[11px] font-bold text-muted/70">أو الدخول بالبيانات</span>
        <span class="h-px flex-1 bg-line"></span>
    </div>

    @if ($errors->any())
        <div class="mb-5 flex items-start gap-3 rounded-2xl border border-danger/20 bg-danger-soft px-4 py-3.5 text-sm text-danger">
            <svg class="mt-0.5 size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 2.35 17.6A1.5 1.5 0 0 0 3.65 20h16.7a1.5 1.5 0 0 0 1.3-2.4L13.7 3.86a1.5 1.5 0 0 0-2.6 0Z"/></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" x-data="{ show: false }" class="space-y-4">
        @csrf

        <div>
            <label for="email" class="label">البريد الإلكتروني</label>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-muted/60">
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/></svg>
                </span>
                <input id="email" name="email" type="email" dir="ltr" autocomplete="email" required
                       value="{{ old('email', 'demo@dukkani.jo') }}"
                       class="input py-3.5 pe-4 ps-11 text-left" placeholder="name@example.com">
            </div>
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between">
                <label for="password" class="label mb-0">كلمة المرور</label>
                <span class="text-[11px] font-medium text-muted/70">كلمة المرور التجريبية: demo1234</span>
            </div>
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-muted/60">
                    <svg class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/></svg>
                </span>
                <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="current-password" required
                       value="demo1234"
                       class="input py-3.5 pe-12 ps-11" placeholder="••••••••">
                <button type="button" @click="show = !show" class="absolute inset-y-0 left-2 my-auto flex size-9 items-center justify-center rounded-full text-muted hover:bg-canvas hover:text-ink" :aria-label="show ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'">
                    <svg x-show="!show" class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                    <svg x-show="show" x-cloak class="size-[18px]" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-between pt-1">
            <label class="flex cursor-pointer items-center gap-2.5 text-xs font-medium text-muted">
                <input type="checkbox" name="remember" class="size-4 rounded border-line text-brand focus:ring-brand/20">
                تذكّرني على هذا الجهاز
            </label>
            <span class="text-[11px] text-muted/60">نسيتِ كلمة المرور؟</span>
        </div>

        <button type="submit" class="btn-primary mt-2 w-full py-3.5">
            تسجيل الدخول
        </button>
    </form>

    <div class="mt-7 rounded-2xl border border-line/70 bg-white/50 px-4 py-3 text-center text-[11px] leading-6 text-muted">
        حساب العرض: <span dir="ltr" class="font-bold text-ink-soft">demo@dukkani.jo</span> — كلمة المرور: <span dir="ltr" class="font-bold text-ink-soft">demo1234</span>
    </div>
@endsection
