@extends('layouts.storefront')

@section('title', 'إتمام الطلب')

@section('content')
    <section class="mx-auto max-w-6xl px-5 pt-8 sm:px-8 sm:pt-10">
        <nav class="flex items-center gap-1.5 text-[10px] font-medium text-muted/70">
            <a href="{{ route('storefront.home', $store) }}" class="transition hover:text-brand">الرئيسية</a>
            <svg class="size-3 text-muted/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
            <a href="{{ route('storefront.cart', $store) }}" class="transition hover:text-brand">السلة</a>
            <svg class="size-3 text-muted/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
            <span class="text-ink-soft">البيانات</span>
        </nav>

        <div class="mt-4 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.18em] text-brand">آخر خطوة</p>
                <h1 class="mt-2 font-display text-[2.1rem] font-bold leading-tight text-ink sm:text-[2.6rem]">إتمام الطلب</h1>
                <p class="mt-2 max-w-xl text-sm leading-7 text-muted">أدخلي بيانات التواصل، وحددي موعد التوصيل أو الاستلام. نؤكد الطلب معك على واتساب قبل بدء التحضير.</p>
            </div>

            <div class="hidden items-center gap-1.5 rounded-full border border-line bg-surface px-2 py-1.5 sm:flex">
                <span class="flex items-center gap-1.5 rounded-full bg-sage-soft px-3 py-1.5 text-[10px] font-bold text-sage">
                    <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                    السلة
                </span>
                <span class="rounded-full bg-brand px-3 py-1.5 text-[10px] font-bold text-white">البيانات</span>
                <span class="rounded-full px-3 py-1.5 text-[10px] font-bold text-muted/60">التأكيد</span>
            </div>
        </div>

        <div class="mt-7 grid gap-5 lg:grid-cols-[1.5fr_0.9fr] lg:items-start" x-data="checkoutPage(@js($checkoutConfig))">
            <form id="checkout-form" method="POST" action="{{ route('storefront.checkout.store', $store) }}" class="space-y-4">
                @csrf

                @if ($errors->any())
                    <div class="flex items-start gap-3 rounded-2xl border border-danger/20 bg-danger-soft px-4 py-3.5 text-xs text-danger">
                        <svg class="mt-0.5 size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 2.35 17.6A1.5 1.5 0 0 0 3.65 20h16.7a1.5 1.5 0 0 0 1.3-2.4L13.7 3.86a1.5 1.5 0 0 0-2.6 0Z"/></svg>
                        <div>
                            <p class="font-bold">راجعي الحقول التالية للمتابعة:</p>
                            <ul class="mt-1 list-inside list-disc space-y-0.5 leading-5">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                <div class="card p-5 sm:p-6">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand text-xs font-bold text-white">١</span>
                            <div>
                                <h2 class="text-sm font-bold text-ink">بيانات التواصل</h2>
                                <p class="mt-0.5 text-[10px] text-muted">نحتاجها لتأكيد الطلب والتوصيل.</p>
                            </div>
                        </div>
                        <button type="button" @click="fillDemo()"
                                class="inline-flex shrink-0 items-center gap-1 rounded-full border border-line bg-canvas px-2.5 py-1.5 text-[9px] font-bold text-muted transition hover:border-brand/30 hover:text-brand">
                            <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456Z"/></svg>
                            تعبئة تجريبية
                        </button>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="name" class="label">الاسم الكامل</label>
                            <input id="name" name="name" type="text" x-model="name" value="{{ old('name') }}"
                                   autocomplete="name" required class="input" placeholder="مثلاً: نور الخالدي">
                            @error('name') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="phone" class="label">رقم الهاتف / واتساب</label>
                            <input id="phone" name="phone" type="tel" dir="ltr" x-model="phone" value="{{ old('phone') }}"
                                   autocomplete="tel" required class="input text-left" placeholder="07 9111 1001">
                            @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <div class="card p-5 sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand text-xs font-bold text-white">٢</span>
                        <div>
                            <h2 class="text-sm font-bold text-ink">طريقة الاستلام</h2>
                            <p class="mt-0.5 text-[10px] text-muted">اختاري التوصيل داخل {{ $store->city }} أو الاستلام من المتجر.</p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-2.5 sm:grid-cols-2">
                        @if ($store->delivery_enabled)
                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border p-4 transition"
                                   :class="fulfillment === 'delivery' ? 'border-brand bg-brand-soft/40 ring-1 ring-brand/15' : 'border-line bg-white hover:border-brand/30'">
                                <input type="radio" name="fulfillment_type" value="delivery" x-model="fulfillment" class="sr-only">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl" :class="fulfillment === 'delivery' ? 'bg-brand text-white' : 'bg-canvas text-muted'">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12"/></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold text-ink">توصيل</span>
                                        <span class="text-[10px] font-bold text-brand">{{ \App\Support\Money::format($store->delivery_fee_fils) }}</span>
                                    </span>
                                    <span class="mt-0.5 block text-[10px] leading-5 text-muted">إلى عنوانك داخل {{ $store->city }}.</span>
                                </span>
                            </label>
                        @endif

                        @if ($store->pickup_enabled)
                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border p-4 transition"
                                   :class="fulfillment === 'pickup' ? 'border-brand bg-brand-soft/40 ring-1 ring-brand/15' : 'border-line bg-white hover:border-brand/30'">
                                <input type="radio" name="fulfillment_type" value="pickup" x-model="fulfillment" class="sr-only">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl" :class="fulfillment === 'pickup' ? 'bg-brand text-white' : 'bg-canvas text-muted'">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z"/></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-bold text-ink">استلام من المتجر</span>
                                        <span class="text-[10px] font-bold text-sage">مجاني</span>
                                    </span>
                                    <span class="mt-0.5 block text-[10px] leading-5 text-muted">{{ $store->address }}، {{ $store->city }}.</span>
                                </span>
                            </label>
                        @endif
                    </div>
                    @error('fulfillment_type') <p class="field-error">{{ $message }}</p> @enderror

                    <div x-show="fulfillment === 'delivery'" x-cloak class="mt-4">
                        <label for="delivery_address" class="label">عنوان التوصيل</label>
                        <textarea id="delivery_address" name="delivery_address" rows="2" x-model="address" maxlength="300"
                                  class="textarea min-h-20 text-xs" placeholder="المنطقة، الشارع، رقم البناية، وأي علامة مميزة…">{{ old('delivery_address') }}</textarea>
                        <p class="mt-1.5 text-[9px] text-muted/60">نرجو كتابة عنوان واضح لتسهيل الوصول في الوقت المحدد.</p>
                        @error('delivery_address') <p class="field-error">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="card p-5 sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand text-xs font-bold text-white">٣</span>
                        <div>
                            <h2 class="text-sm font-bold text-ink">الموعد</h2>
                            <p class="mt-0.5 text-[10px] text-muted">أقل مدة تحضير {{ $schedule['leadHours'] }} ساعة من وقت الطلب.</p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="scheduled_date" class="label">التاريخ</label>
                            <input id="scheduled_date" name="scheduled_date" type="date" x-model="date" :min="earliestDate" required
                                   class="input text-xs" dir="ltr">
                            @error('scheduled_date') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label for="scheduled_time" class="label">الوقت</label>
                            <select id="scheduled_time" name="scheduled_time" x-model="time" :disabled="closed" required class="select text-xs" dir="ltr">
                                @foreach ($schedule['slots'] as $slot)
                                    <option value="{{ $slot }}" :disabled="!availableSlots.includes('{{ $slot }}')">{{ $slot }}</option>
                                @endforeach
                            </select>
                            @error('scheduled_time') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div x-show="closed" x-cloak class="mt-3 flex items-start gap-2 rounded-2xl bg-danger-soft px-3.5 py-2.5 text-[10px] leading-5 text-danger">
                        <svg class="mt-0.5 size-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.86 2.35 17.6A1.5 1.5 0 0 0 3.65 20h16.7a1.5 1.5 0 0 0 1.3-2.4L13.7 3.86a1.5 1.5 0 0 0-2.6 0Z"/></svg>
                        <span x-text="closedReason"></span>
                    </div>

                    <div class="mt-3 flex items-start gap-2 rounded-2xl bg-canvas/70 px-3.5 py-2.5 text-[10px] leading-5 text-muted">
                        <svg class="mt-0.5 size-3.5 shrink-0 text-brand" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/></svg>
                        <span>ساعات العمل: <span class="font-bold text-ink-soft" x-text="hoursLabel"></span>. المواعيد المتاحة تُحدّث تلقائياً حسب اليوم.</span>
                    </div>
                </div>

                <div class="card p-5 sm:p-6">
                    <div class="flex items-center gap-3">
                        <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-brand text-xs font-bold text-white">٤</span>
                        <div>
                            <h2 class="text-sm font-bold text-ink">طريقة الدفع</h2>
                            <p class="mt-0.5 text-[10px] text-muted">لا يوجد دفع إلكتروني في هذا العرض التجريبي.</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-2">
                        @foreach ([
                            ['cash', 'نقداً عند الاستلام', 'تدفعين عند استلام الطلب أو وصوله.', 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
                            ['cliq', 'تحويل CliQ', 'تحويل فوري، ويتطلب عربوناً للتأكيد.', 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z'],
                            ['bank_transfer', 'حوالة بنكية', 'تحويل بنكي، ويتطلب عربوناً للتأكيد.', 'M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z'],
                        ] as [$value, $title, $subtitle, $icon])
                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border p-3.5 transition"
                                   :class="payment === '{{ $value }}' ? 'border-brand bg-brand-soft/40 ring-1 ring-brand/15' : 'border-line bg-white hover:border-brand/30'">
                                <input type="radio" name="payment_method" value="{{ $value }}" x-model="payment" class="sr-only">
                                <span class="flex size-9 shrink-0 items-center justify-center rounded-xl" :class="payment === '{{ $value }}' ? 'bg-brand text-white' : 'bg-canvas text-muted'">
                                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $icon }}"/></svg>
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="flex flex-wrap items-center gap-2">
                                        <span class="text-xs font-bold text-ink">{{ $title }}</span>
                                        @if ($value !== 'cash' && $store->deposit_percent > 0)
                                            <span class="rounded-full bg-gold-soft px-2 py-0.5 text-[8px] font-bold text-[#8a672c]">عربون {{ $store->deposit_percent }}%</span>
                                        @endif
                                    </span>
                                    <span class="mt-0.5 block text-[10px] leading-5 text-muted">{{ $subtitle }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    @error('payment_method') <p class="field-error">{{ $message }}</p> @enderror

                    <div x-show="payment !== 'cash' && depositPercent > 0" x-cloak class="mt-3 rounded-2xl border border-gold/30 bg-gold-soft/50 p-3.5 text-[10px] leading-5 text-[#8a672c]">
                        <div class="flex items-center justify-between gap-2">
                            <span>العربون المطلوب</span>
                            <span class="text-xs font-bold" x-text="money(deposit)"></span>
                        </div>
                        <div class="mt-1 flex items-center justify-between gap-2">
                            <span>المتبقي عند الاستلام</span>
                            <span class="font-bold" x-text="money(balance)"></span>
                        </div>
                        <p class="mt-2 border-t border-gold/20 pt-2">{{ $store->deposit_instructions }}</p>
                    </div>
                </div>

                <div class="card p-5 sm:p-6">
                    <label for="customer_notes" class="label">ملاحظات إضافية</label>
                    <textarea id="customer_notes" name="customer_notes" rows="3" maxlength="500" x-model="notes"
                              class="textarea text-xs" placeholder="أي تفاصيل تحبي نعرفها عن الطلب، المناسبة، أو التوصيل…">{{ old('customer_notes') }}</textarea>
                    <p class="mt-1.5 text-[9px] text-muted/60"><span x-text="500 - notes.length"></span> حرفاً متبقياً</p>
                    @error('customer_notes') <p class="field-error">{{ $message }}</p> @enderror

                    <label class="mt-5 flex cursor-pointer items-start gap-2.5 rounded-2xl bg-canvas/60 p-3.5 text-[10px] leading-5 text-muted">
                        <input type="checkbox" name="consent" value="1" x-model="consent" required
                               class="mt-0.5 size-4 shrink-0 rounded border-line text-brand focus:ring-brand/20">
                        <span>
                            أوافق على استخدام اسمي ورقم هاتفي وعنواني لتجهيز هذا الطلب والتواصل معي بشأنه. هذا عرض تجريبي والبيانات محلية.
                        </span>
                    </label>
                    @error('consent') <p class="field-error">{{ $message }}</p> @enderror
                </div>

                <button type="submit" class="btn-primary w-full py-4" :disabled="closed || !time">
                    تأكيد الطلب — <span x-text="money(total)"></span>
                </button>
                <p class="pb-2 text-center text-[9px] leading-5 text-muted/60">
                    بالضغط على «تأكيد الطلب» يتم إنشاء طلب تجريبي فقط. لا تتم أي مدفوعات أو رسائل حقيقية.
                </p>
            </form>

            <aside class="space-y-3 lg:sticky lg:top-24">
                <div class="card overflow-hidden">
                    <div class="flex items-center justify-between border-b border-line/70 px-5 py-4">
                        <h2 class="text-sm font-bold text-ink">ملخص الطلب</h2>
                        <a href="{{ route('storefront.cart', $store) }}" class="text-[10px] font-bold text-brand">تعديل</a>
                    </div>

                    <div class="max-h-72 space-y-3 overflow-y-auto px-5 py-4">
                        @foreach ($items as $item)
                            <div class="flex gap-3">
                                <img src="{{ $item['product']->imageUrl() }}" alt="{{ $item['product']->name }}" class="size-12 shrink-0 rounded-xl object-cover">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="truncate text-[11px] font-bold text-ink">{{ $item['product']->name }}</p>
                                        <span class="shrink-0 text-[10px] font-bold text-ink">{{ \App\Support\Money::format($item['line_total_fils']) }}</span>
                                    </div>
                                    <p class="mt-0.5 text-[9px] text-muted">الكمية: {{ $item['quantity'] }}</p>
                                    @if (count($item['options']))
                                        <p class="mt-0.5 truncate text-[9px] text-muted/70">
                                            {{ collect($item['options'])->map(fn ($o) => $o['name'].': '.$o['value'])->join(' • ') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="space-y-2.5 border-t border-line/70 px-5 py-4 text-[11px]">
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted">المجموع الفرعي</span>
                            <span class="font-bold text-ink" x-text="money(subtotal)">{{ \App\Support\Money::format($subtotal) }}</span>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="text-muted" x-text="fulfillment === 'delivery' ? 'التوصيل' : 'الاستلام'">التوصيل</span>
                            <span class="font-bold text-ink" x-text="fee > 0 ? money(fee) : 'مجاني'">{{ \App\Support\Money::format($store->delivery_fee_fils) }}</span>
                        </div>
                        <div class="divider"></div>
                        <div class="flex items-center justify-between gap-3 pt-1">
                            <span class="text-xs font-bold text-ink">الإجمالي</span>
                            <span class="font-display text-xl font-bold text-brand" x-text="money(total)">{{ \App\Support\Money::format($subtotal + $store->delivery_fee_fils) }}</span>
                        </div>
                        <div x-show="payment !== 'cash' && depositPercent > 0" x-cloak class="flex items-center justify-between gap-3 rounded-xl bg-gold-soft/60 px-3 py-2 text-[10px] text-[#8a672c]">
                            <span>عربون مطلوب الآن</span>
                            <span class="font-bold" x-text="money(deposit)"></span>
                        </div>
                    </div>

                    <div class="px-5 pb-5">
                        <button type="submit" form="checkout-form" class="btn-primary w-full py-3.5" :disabled="closed || !time">
                            تأكيد الطلب
                        </button>
                        <p class="mt-2.5 text-center text-[9px] leading-5 text-muted/60">سنتواصل معك على واتساب لتأكيد التفاصيل قبل التحضير.</p>
                    </div>
                </div>

                <div class="flex items-start gap-2.5 rounded-2xl border border-line/70 bg-surface px-4 py-3 text-[10px] leading-5 text-muted">
                    <svg class="mt-0.5 size-4 shrink-0 text-sage" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z"/></svg>
                    <span>طلبك محفوظ في هذا العرض التجريبي فقط. لا نشارك بياناتك مع أي طرف، ولا نرسل رسائل حقيقية.</span>
                </div>

                <a href="{{ $store->whatsappLink('مرحباً، عندي سؤال قبل إتمام الطلب.') }}" target="_blank" rel="noopener"
                   class="flex items-center justify-center gap-2 rounded-2xl border border-line/70 bg-surface px-4 py-3 text-[10px] font-bold text-ink-soft transition hover:border-sage/30 hover:text-sage">
                    <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    سؤال قبل الطلب؟ راسلينا
                </a>
            </aside>
        </div>
    </section>
@endsection