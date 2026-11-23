@extends('layouts.admin')

@section('title', 'متجر جديد')
@section('page-title', 'متجر جديد')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-display text-[2rem] font-bold leading-tight text-ink">متجر جديد</h1>
            <p class="mt-2 max-w-xl text-sm leading-6 text-muted">
                أنشئ المتجر وحساب دخول المالك في خطوة واحدة. تُنشأ ساعات العمل الافتراضية تلقائياً،
                ويستطيع المالك تسجيل الدخول فوراً بالبريد وكلمة المرور أدناه.
            </p>
        </div>

        <a href="{{ route('admin.stores.index') }}" class="btn-secondary btn-sm">
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
            رجوع للمتاجر
        </a>
    </div>

    @if ($errors->any())
        <div class="mt-5 flex items-start gap-3 rounded-2xl border border-danger/20 bg-danger-soft px-4 py-3.5 text-sm text-danger">
            <svg class="mt-0.5 size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 2.35 17.6A1.5 1.5 0 0 0 3.65 20h16.7a1.5 1.5 0 0 0 1.3-2.4L13.7 3.86a1.5 1.5 0 0 0-2.6 0Z"/></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.stores.store') }}" class="mt-6 space-y-4">
        @csrf

        <div class="card p-5 sm:p-6">
            <h2 class="text-sm font-bold text-ink">المتجر</h2>
            <p class="mt-1 text-[10px] text-muted">ما يراه العملاء في رابط المتجر.</p>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="store_name">اسم المتجر</label>
                    <input id="store_name" type="text" name="store_name" value="{{ old('store_name') }}" class="input" maxlength="120" required>
                    @error('store_name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="slug">رابط المتجر</label>
                    <div class="flex items-center gap-1.5">
                        <span class="shrink-0 text-[11px] font-bold text-muted/70" dir="ltr">/s/</span>
                        <input id="slug" type="text" name="slug" value="{{ old('slug') }}" class="input !py-2.5" dir="ltr" maxlength="80" placeholder="my-sweets" required>
                    </div>
                    <p class="mt-1 text-[10px] text-muted/70">حروف إنجليزية صغيرة وأرقام وشرطات فقط.</p>
                    @error('slug') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="phone">رقم الهاتف</label>
                    <input id="phone" type="text" name="phone" value="{{ old('phone') }}" class="input" dir="ltr" maxlength="30" placeholder="0599123456" required>
                    @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="whatsapp">رقم الواتساب <span class="font-normal text-muted/60">(اختياري)</span></label>
                    <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp') }}" class="input" dir="ltr" maxlength="30" placeholder="970599123456">
                    @error('whatsapp') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="city">المدينة</label>
                    <input id="city" type="text" name="city" value="{{ old('city', 'عمّان') }}" class="input" maxlength="100" required>
                    @error('city') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="country_code">الدولة</label>
                    <select id="country_code" name="country_code" class="input" required>
                        @foreach ($countries as $code => $country)
                            <option value="{{ $code }}" @selected((string) old('country_code', '962') === (string) $code)>
                                {{ $country['name'] }} (+{{ $code }})
                            </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-[10px] text-muted/70">العملة والمنطقة الزمنية تُضبطان تلقائياً حسب الدولة.</p>
                    @error('country_code') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        <div class="card p-5 sm:p-6">
            <h2 class="text-sm font-bold text-ink">حساب دخول المالك</h2>
            <p class="mt-1 text-[10px] text-muted">بهذه البيانات يدخل المالك إلى لوحة التحكم الخاصة بمتجره.</p>

            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                    <label class="label" for="owner_name">اسم المالك</label>
                    <input id="owner_name" type="text" name="owner_name" value="{{ old('owner_name') }}" class="input" maxlength="120" required>
                    @error('owner_name') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="email">البريد الإلكتروني</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="input" dir="ltr" maxlength="190" autocomplete="off" required>
                    @error('email') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="password">كلمة المرور</label>
                    <input id="password" type="password" name="password" class="input" dir="ltr" minlength="8" autocomplete="new-password" required>
                    <p class="mt-1 text-[10px] text-muted/70">8 أحرف على الأقل.</p>
                    @error('password') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label" for="password_confirmation">تأكيد كلمة المرور</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="input" dir="ltr" minlength="8" autocomplete="new-password" required>
                </div>
            </div>
        </div>

        <div class="card p-5 sm:p-6">
            <label class="flex items-start gap-3">
                <input type="checkbox" name="start_trial" value="1" class="mt-0.5 size-4 rounded border-line text-brand"
                       @checked(! old('_token') || old('start_trial'))>
                <span>
                    <span class="block text-sm font-bold text-ink">ابدأ تجربة Starter المجانية {{ $trialDays }} أيام</span>
                    <span class="mt-0.5 block text-[11px] leading-5 text-muted">
                        طلبات وتقارير بلا حد خلال التجربة، مع سقف 12 منتجاً. بعدها يعود المتجر تلقائياً لحدود الخطة المجانية.
                    </span>
                </span>
            </label>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            <button class="btn-primary">إنشاء المتجر</button>
            <a href="{{ route('admin.stores.index') }}" class="btn-secondary">إلغاء</a>
        </div>
    </form>
@endsection
