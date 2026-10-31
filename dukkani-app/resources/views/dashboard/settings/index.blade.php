@extends('layouts.dashboard')

@section('title', 'الإعدادات')
@section('page-title', 'الإعدادات')

@section('content')
    @php
        use App\Support\Money;

        $validTabs = ['profile', 'delivery', 'hours'];
        $activeTab = in_array(request('tab'), $validTabs, true) ? request('tab') : 'profile';
        $storeUrl = route('storefront.home', $store);
        $feeValue = rtrim(rtrim(number_format($store->delivery_fee_fils / 1000, 3, '.', ''), '0'), '.');
    @endphp

    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-display text-[2rem] font-bold leading-tight text-ink sm:text-[2.35rem]">الإعدادات</h1>
            <p class="mt-2 max-w-xl text-sm leading-6 text-muted">
                اضبطي هوية متجرك، شروط التوصيل والدفع، وساعات عملك — كلها تظهر مباشرة في واجهة العميل.
            </p>
        </div>

        <a href="{{ $storeUrl }}" target="_blank" rel="noopener" class="btn-secondary btn-sm">
            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 19.5 15-15m0 0H8.25m11.25 0v11.25"/></svg>
            معاينة المتجر
        </a>
    </div>

    <div x-data="{ tab: '{{ $activeTab }}' }" class="mt-6 grid gap-4 lg:grid-cols-[1.4fr_0.6fr] lg:items-start">
        <div class="space-y-4">
            {{-- Tabs --}}
            <div class="flex gap-2 overflow-x-auto pb-1 no-scrollbar">
                @foreach ([
                    ['key' => 'profile', 'label' => 'معلومات المتجر', 'icon' => 'M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z'],
                    ['key' => 'delivery', 'label' => 'التوصيل والدفع', 'icon' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
                    ['key' => 'hours', 'label' => 'ساعات العمل', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                ] as $tab)
                    <button type="button" @click="tab = '{{ $tab['key'] }}'"
                            class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3.5 py-2 text-[11px] font-bold transition"
                            :class="tab === '{{ $tab['key'] }}' ? 'border-brand bg-brand text-white' : 'border-line bg-surface text-ink-soft hover:border-brand/30 hover:text-ink'">
                        <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $tab['icon'] }}"/></svg>
                        {{ $tab['label'] }}
                    </button>
                @endforeach
            </div>

            {{-- Profile --}}
            <div x-show="tab === 'profile'" x-cloak>
                <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data" class="card p-5 sm:p-6">
                    @csrf
                    @method('PATCH')

                    <h2 class="text-sm font-bold text-ink">معلومات المتجر</h2>
                    <p class="mt-1 text-[10px] text-muted">تظهر هذه المعلومات في الصفحة الرئيسية لمتجرك.</p>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="label" for="name">اسم المتجر</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $store->name) }}" class="input" maxlength="100" required>
                            @error('name') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label" for="slug">رابط المتجر</label>
                            <div class="flex items-center gap-1.5">
                                <span class="shrink-0 text-[11px] font-bold text-muted/70" dir="ltr">/s/</span>
                                <input id="slug" type="text" name="slug" value="{{ old('slug', $store->slug) }}" class="input !py-2.5" dir="ltr" maxlength="100" required>
                            </div>
                            @error('slug') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="label" for="tagline">العبارة التعريفية</label>
                            <input id="tagline" type="text" name="tagline" value="{{ old('tagline', $store->tagline) }}" class="input" maxlength="160" placeholder="حلويات منزلية تُخبز على مهل...">
                            @error('tagline') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="label" for="description">نبذة عن المتجر</label>
                            <textarea id="description" name="description" rows="4" class="textarea" maxlength="2000">{{ old('description', $store->description) }}</textarea>
                            @error('description') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label" for="phone">رقم الهاتف</label>
                            <input id="phone" type="text" name="phone" value="{{ old('phone', $store->phone) }}" class="input" dir="ltr" maxlength="30" required>
                            @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label" for="whatsapp">رقم الواتساب</label>
                            <input id="whatsapp" type="text" name="whatsapp" value="{{ old('whatsapp', $store->whatsapp) }}" class="input" dir="ltr" maxlength="30" placeholder="962790000000">
                            @error('whatsapp') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label" for="instagram">إنستغرام</label>
                            <input id="instagram" type="text" name="instagram" value="{{ old('instagram', $store->instagram) }}" class="input" dir="ltr" maxlength="100" placeholder="amalbakes.jo">
                            @error('instagram') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label" for="city">المدينة</label>
                            <input id="city" type="text" name="city" value="{{ old('city', $store->city) }}" class="input" maxlength="100" required>
                            @error('city') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="label" for="address">العنوان</label>
                            <input id="address" type="text" name="address" value="{{ old('address', $store->address) }}" class="input" maxlength="200" placeholder="المنطقة، الشارع، رقم البناية">
                            @error('address') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Images --}}
                    <div class="mt-5 border-t border-line/60 pt-5">
                        <h3 class="text-sm font-bold text-ink">الشعار والغلاف</h3>

                        <div class="mt-4 grid gap-4 sm:grid-cols-2"
                             x-data="{
                                 logo: @js($store->logoUrl() ?? ''),
                                 cover: @js($store->coverUrl() ?? ''),
                                 pick(event, key) { const f = event.target.files[0]; if (f) { this[key] = URL.createObjectURL(f); } }
                             }">
                            <div>
                                <label class="label !text-[10px] !text-muted">الشعار</label>
                                <div class="flex items-center gap-3">
                                    <span class="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-line bg-canvas">
                                        <template x-if="logo">
                                            <img :src="logo" alt="الشعار" class="size-full object-cover">
                                        </template>
                                        <template x-if="! logo">
                                            <span class="text-[9px] text-muted/60">بدون</span>
                                        </template>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <input type="file" name="logo_file" accept="image/*" @change="pick($event, 'logo')" class="block w-full cursor-pointer rounded-xl border border-dashed border-line bg-white px-2.5 py-2 text-[10px] text-muted file:me-2 file:rounded-full file:border-0 file:bg-brand-soft file:px-2.5 file:py-1 file:text-[9px] file:font-bold file:text-brand-dark hover:border-brand/30">
                                        <input type="url" name="logo_url" value="{{ old('logo_url', str_starts_with((string) $store->logo_path, 'http') ? $store->logo_path : '') }}" dir="ltr" class="input mt-2 !py-2 !text-[10px]" placeholder="https://...">
                                        @if ($store->logo_path)
                                            <label class="mt-1.5 flex items-center gap-1.5 text-[9px] text-muted">
                                                <input type="checkbox" name="remove_logo" value="1" class="size-3 rounded border-line text-brand focus:ring-brand/30">
                                                إزالة الشعار الحالي
                                            </label>
                                        @endif
                                    </div>
                                </div>
                                @error('logo_file') <p class="field-error">{{ $message }}</p> @enderror
                                @error('logo_url') <p class="field-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="label !text-[10px] !text-muted">صورة الغلاف</label>
                                <div class="flex items-center gap-3">
                                    <span class="flex h-16 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-line bg-canvas">
                                        <template x-if="cover">
                                            <img :src="cover" alt="الغلاف" class="size-full object-cover">
                                        </template>
                                        <template x-if="! cover">
                                            <span class="text-[9px] text-muted/60">بدون</span>
                                        </template>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <input type="file" name="cover_file" accept="image/*" @change="pick($event, 'cover')" class="block w-full cursor-pointer rounded-xl border border-dashed border-line bg-white px-2.5 py-2 text-[10px] text-muted file:me-2 file:rounded-full file:border-0 file:bg-brand-soft file:px-2.5 file:py-1 file:text-[9px] file:font-bold file:text-brand-dark hover:border-brand/30">
                                        <input type="url" name="cover_url" value="{{ old('cover_url', str_starts_with((string) $store->cover_path, 'http') ? $store->cover_path : '') }}" dir="ltr" class="input mt-2 !py-2 !text-[10px]" placeholder="https://...">
                                        @if ($store->cover_path)
                                            <label class="mt-1.5 flex items-center gap-1.5 text-[9px] text-muted">
                                                <input type="checkbox" name="remove_cover" value="1" class="size-3 rounded border-line text-brand focus:ring-brand/30">
                                                إزالة الغلاف الحالي
                                            </label>
                                        @endif
                                    </div>
                                </div>
                                @error('cover_file') <p class="field-error">{{ $message }}</p> @enderror
                                @error('cover_url') <p class="field-error">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button class="btn-primary">حفظ معلومات المتجر</button>
                    </div>
                </form>
            </div>

            {{-- Delivery --}}
            <div x-show="tab === 'delivery'" x-cloak>
                <form method="POST" action="{{ route('settings.store') }}" class="card p-5 sm:p-6">
                    @csrf
                    @method('PATCH')

                    <h2 class="text-sm font-bold text-ink">التوصيل والدفع</h2>
                    <p class="mt-1 text-[10px] text-muted">تتحكم هذه الإعدادات بخيارات العميل عند إتمام الطلب.</p>

                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <label class="flex items-start gap-3 rounded-2xl border border-line/70 bg-canvas/40 p-3.5">
                            <input type="hidden" name="pickup_enabled" value="0">
                            <input type="checkbox" name="pickup_enabled" value="1" @checked($store->pickup_enabled) class="mt-0.5 size-4 rounded border-line text-brand focus:ring-brand/30">
                            <span>
                                <span class="block text-[11px] font-bold text-ink">الاستلام من المتجر</span>
                                <span class="mt-0.5 block text-[9px] leading-5 text-muted">يستطيع العميل اختيار الاستلام بدون رسوم.</span>
                            </span>
                        </label>

                        <label class="flex items-start gap-3 rounded-2xl border border-line/70 bg-canvas/40 p-3.5">
                            <input type="hidden" name="delivery_enabled" value="0">
                            <input type="checkbox" name="delivery_enabled" value="1" @checked($store->delivery_enabled) class="mt-0.5 size-4 rounded border-line text-brand focus:ring-brand/30">
                            <span>
                                <span class="block text-[11px] font-bold text-ink">التوصيل</span>
                                <span class="mt-0.5 block text-[9px] leading-5 text-muted">خصّصي رسوم التوصيل أدناه.</span>
                            </span>
                        </label>
                    </div>
                    @error('delivery_enabled') <p class="field-error">{{ $message }}</p> @enderror

                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div>
                            <label class="label" for="delivery_fee">رسوم التوصيل (د.أ)</label>
                            <input id="delivery_fee" type="number" name="delivery_fee" value="{{ old('delivery_fee', $feeValue) }}" step="0.001" min="0" max="100" class="input" required>
                            @error('delivery_fee') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label" for="deposit_percent">نسبة العربون (٪)</label>
                            <input id="deposit_percent" type="number" name="deposit_percent" value="{{ old('deposit_percent', $store->deposit_percent) }}" min="0" max="100" class="input" required>
                            <p class="mt-1.5 text-[9px] text-muted/70">تُطبّق على الدفع عبر CliQ أو التحويل البنكي.</p>
                            @error('deposit_percent') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label" for="lead_time_hours">أقل مدة تحضير (ساعات)</label>
                            <input id="lead_time_hours" type="number" name="lead_time_hours" value="{{ old('lead_time_hours', $store->lead_time_hours) }}" min="1" max="720" class="input" required>
                            @error('lead_time_hours') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="label" for="deposit_instructions">تعليمات العربون</label>
                        <textarea id="deposit_instructions" name="deposit_instructions" rows="3" class="textarea" maxlength="1000" placeholder="كيف يحوّل العميل العربون...">{{ old('deposit_instructions', $store->deposit_instructions) }}</textarea>
                        @error('deposit_instructions') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="mt-5 flex justify-end">
                        <button class="btn-primary">حفظ إعدادات التوصيل</button>
                    </div>
                </form>
            </div>

            {{-- Hours --}}
            <div x-show="tab === 'hours'" x-cloak class="space-y-4">
                <form method="POST" action="{{ route('settings.hours') }}" class="card p-5 sm:p-6">
                    @csrf
                    @method('PATCH')

                    <h2 class="text-sm font-bold text-ink">ساعات العمل</h2>
                    <p class="mt-1 text-[10px] text-muted">تُستخدم هذه الساعات لاقتراح مواعيد التحضير والاستلام على العميل.</p>

                    <div class="mt-4 space-y-2.5">
                        @foreach ($hours as $hour)
                            @php $day = $hour->day_of_week; @endphp
                            <div x-data="{ closed: {{ $hour->is_closed ? 'true' : 'false' }} }"
                                 class="flex flex-wrap items-center gap-3 rounded-2xl border border-line/70 bg-canvas/30 px-4 py-3">
                                <span class="w-16 shrink-0 text-[11px] font-bold text-ink">{{ $hour->dayName() }}</span>

                                <label class="flex items-center gap-2 text-[10px] font-bold text-ink-soft">
                                    <input type="hidden" name="hours[{{ $day }}][is_closed]" value="0">
                                    <input type="checkbox" name="hours[{{ $day }}][is_closed]" value="1" x-model="closed" @checked($hour->is_closed) class="size-3.5 rounded border-line text-brand focus:ring-brand/30">
                                    مغلق
                                </label>

                                <div class="flex items-center gap-2" :class="closed && 'opacity-40'">
                                    <input type="time" name="hours[{{ $day }}][open_time]" value="{{ $hour->open_time ? substr($hour->open_time, 0, 5) : '' }}" :disabled="closed" class="input w-[7.5rem] !py-1.5 !text-xs" dir="ltr">
                                    <span class="text-muted/60">—</span>
                                    <input type="time" name="hours[{{ $day }}][close_time]" value="{{ $hour->close_time ? substr($hour->close_time, 0, 5) : '' }}" :disabled="closed" class="input w-[7.5rem] !py-1.5 !text-xs" dir="ltr">
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @php
                        $hourErrors = collect($errors->getBag('default')->getMessages())
                            ->filter(fn ($messages, $key) => str_starts_with($key, 'hours'))
                            ->flatten();
                    @endphp
                    @if ($hourErrors->isNotEmpty())
                        <div class="mt-4 rounded-2xl border border-danger/20 bg-danger-soft px-4 py-3 text-[10px] leading-5 text-danger">
                            <ul class="list-inside list-disc space-y-0.5">
                                @foreach ($hourErrors as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-5 flex justify-end">
                        <button class="btn-primary">حفظ ساعات العمل</button>
                    </div>
                </form>

                <div class="card p-5 sm:p-6">
                    <h2 class="text-sm font-bold text-ink">التواريخ المحظورة</h2>
                    <p class="mt-1 text-[10px] text-muted">أيام لا تستقبلين فيها طلبات (مناسبات، صيانة، سفر...).</p>

                    <form method="POST" action="{{ route('settings.blocked.store') }}" class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-end">
                        @csrf
                        <div>
                            <label class="label !mb-1.5 !text-[10px] !text-muted">التاريخ</label>
                            <input type="date" name="date" value="{{ old('date') }}" min="{{ now()->toDateString() }}" class="input !py-2.5" required>
                        </div>
                        <div class="flex-1">
                            <label class="label !mb-1.5 !text-[10px] !text-muted">السبب (اختياري)</label>
                            <input type="text" name="reason" value="{{ old('reason') }}" class="input !py-2.5" maxlength="120" placeholder="مثال: مغلق لطلب مناسبة خاصة">
                        </div>
                        <button class="btn-secondary btn-sm">حظر التاريخ</button>
                    </form>
                    @error('date') <p class="field-error">{{ $message }}</p> @enderror
                    @error('reason') <p class="field-error">{{ $message }}</p> @enderror

                    <div class="mt-4 space-y-2">
                        @forelse ($blockedDates as $blocked)
                            <div class="flex items-center justify-between gap-3 rounded-2xl border border-line/70 bg-canvas/30 px-4 py-2.5">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-bold text-ink">{{ $blocked->date->translatedFormat('l، j F Y') }}</p>
                                    @if ($blocked->reason)
                                        <p class="mt-0.5 text-[9px] text-muted">{{ $blocked->reason }}</p>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('settings.blocked.destroy', $blocked) }}" onsubmit="return confirm('إلغاء حظر هذا التاريخ؟')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="icon-btn !size-8 text-muted hover:text-danger" aria-label="إلغاء الحظر">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="rounded-2xl border border-dashed border-line bg-canvas/30 px-4 py-6 text-center text-[10px] text-muted">لا توجد تواريخ محظورة.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4 lg:sticky lg:top-24">
            <div class="card p-5" x-data="{ copied: false }">
                <h2 class="text-sm font-bold text-ink">رابط متجرك</h2>
                <p class="mt-1 text-[10px] text-muted">شاركي هذا الرابط في إنستغرام أو واتساب.</p>

                <div class="mt-3 rounded-2xl border border-line bg-canvas/50 px-3 py-2.5">
                    <p class="truncate text-[10px] font-bold text-ink-soft" dir="ltr">{{ $storeUrl }}</p>
                </div>

                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button type="button"
                            @click="navigator.clipboard?.writeText(@js($storeUrl)).then(() => { copied = true; setTimeout(() => copied = false, 2000); }).catch(() => {})"
                            class="btn-secondary btn-sm">
                        <span x-text="copied ? 'تم النسخ' : 'نسخ الرابط'">نسخ الرابط</span>
                    </button>
                    <a href="{{ $store->whatsappLink('تفضلوا بزيارة متجرنا: '.$storeUrl) }}" target="_blank" rel="noopener" class="btn-secondary btn-sm">
                        واتساب
                    </a>
                </div>
            </div>

            <div class="card p-5">
                <h2 class="text-sm font-bold text-ink">ملخص</h2>
                <dl class="mt-3 space-y-2.5 text-[11px]">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">الاستلام من المتجر</dt>
                        <dd class="font-bold {{ $store->pickup_enabled ? 'text-sage' : 'text-muted/60' }}">{{ $store->pickup_enabled ? 'مفعّل' : 'معطّل' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">التوصيل</dt>
                        <dd class="font-bold {{ $store->delivery_enabled ? 'text-sage' : 'text-muted/60' }}">{{ $store->delivery_enabled ? 'مفعّل' : 'معطّل' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">رسوم التوصيل</dt>
                        <dd class="font-bold text-ink-soft">{{ Money::format($store->delivery_fee_fils) }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">العربون</dt>
                        <dd class="font-bold text-ink-soft">{{ $store->deposit_percent }}٪</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">أقل مدة تحضير</dt>
                        <dd class="font-bold text-ink-soft">{{ $store->lead_time_hours }} ساعة</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl bg-gradient-to-bl from-gold-soft/80 to-brand-soft/50 p-3.5">
                <div class="flex items-center gap-2 text-[10px] font-bold text-[#8a672c]">
                    <span class="size-1.5 rounded-full bg-gold"></span>
                    عرض تجريبي
                </div>
                <p class="mt-1.5 text-[10px] leading-5 text-muted">أي تغيير هنا يظهر في المتجر التجريبي مباشرة، بدون أي تأثير حقيقي.</p>
            </div>
        </div>
    </div>
@endsection