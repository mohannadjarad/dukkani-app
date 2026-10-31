@extends('layouts.dashboard')

@php
    $isEdit = $product !== null;
    $title = $isEdit ? 'تعديل '.$product->name : 'منتج جديد';
@endphp

@section('title', $title)
@section('page-title', $isEdit ? 'تعديل منتج' : 'منتج جديد')

@section('content')
    @php
        use App\Support\Money;

        $action = $isEdit ? route('products.update', $product) : route('products.store');
        $activeDefault = $errors->any() ? (bool) old('is_active') : ($product?->is_active ?? true);
        $featuredDefault = $errors->any() ? (bool) old('is_featured') : ($product?->is_featured ?? false);
        $priceValue = old('price', $product ? rtrim(rtrim(number_format($product->price_fils / 1000, 3, '.', ''), '0'), '.') : '');
        $imageUrlValue = old('image_url', $product && str_starts_with((string) $product->image_path, 'http') ? $product->image_path : '');
        $preview = $product?->imageUrl() ?? '';
    @endphp

    <nav class="flex items-center gap-1.5 text-[10px] font-medium text-muted/70">
        <a href="{{ route('products.index') }}" class="transition hover:text-brand">المنتجات</a>
        <svg class="size-3 text-muted/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
        <span class="text-ink-soft">{{ $isEdit ? 'تعديل' : 'إضافة' }}</span>
    </nav>

    <div class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="font-display text-[2rem] font-bold leading-tight text-ink sm:text-[2.35rem]">{{ $isEdit ? 'تعديل المنتج' : 'منتج جديد' }}</h1>
            <p class="mt-2 max-w-xl text-sm leading-6 text-muted">
                {{ $isEdit ? 'حدّثي التفاصيل أو الخيارات، وسيظهر التغيير فوراً في متجرك.' : 'أضيفي صنفاً جديداً إلى منيوك مع سعره وخياراته.' }}
            </p>
        </div>
    </div>

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="mt-6">
        @csrf
        @if ($isEdit)
            @method('PATCH')
        @endif

        <div class="grid gap-4 lg:grid-cols-[1.35fr_0.65fr] lg:items-start">
            <div class="space-y-4">
                {{-- Basic info --}}
                <div class="card p-5 sm:p-6">
                    <h2 class="text-sm font-bold text-ink">المعلومات الأساسية</h2>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="label" for="name">اسم المنتج</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $product?->name) }}" class="input" placeholder="مثال: كيكة العسل واللوز" maxlength="120" required>
                            @error('name') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="label" for="slug">رابط المنتج <span class="font-normal text-muted/70">(اختياري)</span></label>
                            <div class="flex items-center gap-2">
                                <span class="shrink-0 text-[10px] text-muted/70" dir="ltr">/product/</span>
                                <input id="slug" type="text" name="slug" value="{{ old('slug', $product?->slug) }}" class="input !py-2.5" dir="ltr" placeholder="honey-almond-cake" maxlength="140">
                            </div>
                            <p class="mt-1.5 text-[9px] leading-5 text-muted/70">اتركيه فارغاً لتوليده تلقائياً. استخدمي أحرفاً إنجليزية للرابط الأجمل.</p>
                            @error('slug') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label class="label" for="description">الوصف</label>
                            <textarea id="description" name="description" rows="4" class="textarea" maxlength="2000" placeholder="المكونات، الحجم، ومتى تُحضّر...">{{ old('description', $product?->description) }}</textarea>
                            @error('description') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label" for="price">السعر (د.أ)</label>
                            <input id="price" type="number" name="price" value="{{ $priceValue }}" step="0.001" min="0.1" max="5000" class="input" placeholder="18.500" required>
                            @error('price') <p class="field-error">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="label" for="category_id">التصنيف</label>
                            <select id="category_id" name="category_id" class="select">
                                <option value="">بدون تصنيف</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected((string) old('category_id', $product?->category_id) === (string) $category->id)>{{ $category->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="field-error">{{ $message }}</p> @enderror
                            @if ($categories->isEmpty())
                                <p class="mt-1.5 text-[9px] text-muted/70">لا توجد تصنيفات — أضيفيها من صفحة المنتجات.</p>
                            @endif
                        </div>

                        <div>
                            <label class="label" for="lead_time_hours">مدة التحضير (ساعات)</label>
                            <input id="lead_time_hours" type="number" name="lead_time_hours" value="{{ old('lead_time_hours', $product?->lead_time_hours) }}" min="1" max="720" class="input" placeholder="{{ $store->lead_time_hours }}">
                            <p class="mt-1.5 text-[9px] text-muted/70">اتركيه فارغاً لاستخدام مدة المتجر ({{ $store->lead_time_hours }} ساعة).</p>
                            @error('lead_time_hours') <p class="field-error">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                {{-- Image --}}
                <div class="card p-5 sm:p-6" x-data="{
                    preview: @js($preview),
                    fallback: @js(asset('images/placeholder-bakery.svg')),
                    pick(event) {
                        const file = event.target.files && event.target.files[0];
                        if (file) { this.preview = URL.createObjectURL(file); }
                    }
                }">
                    <h2 class="text-sm font-bold text-ink">صورة المنتج</h2>
                    <p class="mt-1 text-[10px] text-muted">ارفعي صورة واضحة، أو الصقي رابط صورة. النسبة الأفضل 4:3.</p>

                    <div class="mt-4 grid gap-4 sm:grid-cols-[10rem_1fr]">
                        <div class="overflow-hidden rounded-2xl border border-line bg-canvas">
                            <img :src="preview || fallback" alt="معاينة الصورة" class="aspect-[4/3] w-full object-cover">
                        </div>

                        <div class="space-y-3">
                            <div>
                                <label class="label !mb-1.5 !text-[10px] !text-muted">رفع صورة</label>
                                <input type="file" name="image_file" accept="image/*" @change="pick($event)"
                                       class="block w-full cursor-pointer rounded-2xl border border-dashed border-line bg-white px-3 py-2.5 text-[11px] text-muted file:me-3 file:rounded-full file:border-0 file:bg-brand-soft file:px-3 file:py-1.5 file:text-[10px] file:font-bold file:text-brand-dark hover:border-brand/30">
                                @error('image_file') <p class="field-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="label !mb-1.5 !text-[10px] !text-muted">أو رابط صورة</label>
                                <input type="url" name="image_url" value="{{ $imageUrlValue }}" dir="ltr" class="input !py-2.5" placeholder="https://example.com/cake.jpg" @input="preview = $event.target.value || fallback">
                                @error('image_url') <p class="field-error">{{ $message }}</p> @enderror
                            </div>

                            @if ($isEdit && $product->image_path)
                                <label class="flex items-center gap-2 text-[10px] text-muted">
                                    <input type="checkbox" name="remove_image" value="1" class="size-3.5 rounded border-line text-brand focus:ring-brand/30">
                                    إزالة الصورة الحالية
                                </label>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Options --}}
                <div class="card p-5 sm:p-6" x-data="optionEditor({{ Js::from($options) }})">
                    @php
                        $optionErrors = collect($errors->getBag('default')->getMessages())
                            ->filter(fn ($messages, $key) => str_starts_with($key, 'options'))
                            ->flatten();
                    @endphp

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h2 class="text-sm font-bold text-ink">الخيارات والإضافات</h2>
                            <p class="mt-1 text-[10px] text-muted">مثل الحجم، النوع، أو إضافات مدفوعة.</p>
                        </div>
                        <button type="button" @click="addOption()" class="btn-secondary btn-sm">
                            <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            خيار جديد
                        </button>
                    </div>

                    @if ($optionErrors->isNotEmpty())
                        <div class="mt-4 rounded-2xl border border-danger/20 bg-danger-soft px-4 py-3 text-[10px] leading-5 text-danger">
                            <p class="font-bold">راجعي الخيارات:</p>
                            <ul class="mt-1 list-inside list-disc space-y-0.5">
                                @foreach ($optionErrors as $message)
                                    <li>{{ $message }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="mt-4 space-y-3">
                        <template x-for="(option, oIndex) in options" :key="oIndex">
                            <div class="rounded-2xl border border-line/80 bg-canvas/40 p-4">
                                <div class="flex flex-wrap items-end gap-2.5">
                                    <div class="min-w-[10rem] flex-1">
                                        <label class="label !mb-1.5 !text-[10px] !text-muted">اسم الخيار</label>
                                        <input type="text" :name="'options['+oIndex+'][name]'" x-model="option.name" class="input !py-2.5" placeholder="مثال: الحجم" maxlength="60">
                                    </div>
                                    <div class="w-[9.5rem]">
                                        <label class="label !mb-1.5 !text-[10px] !text-muted">النوع</label>
                                        <select :name="'options['+oIndex+'][type]'" x-model="option.type" class="select !py-2.5">
                                            <option value="single">اختيار واحد</option>
                                            <option value="multiple">اختيار متعدد</option>
                                        </select>
                                    </div>
                                    <label class="flex h-[2.7rem] items-center gap-2 rounded-2xl border border-line bg-white px-3 text-[10px] font-bold text-ink-soft">
                                        <input type="hidden" :name="'options['+oIndex+'][is_required]'" value="0">
                                        <input type="checkbox" :name="'options['+oIndex+'][is_required]'" value="1" x-model="option.is_required" class="size-3.5 rounded border-line text-brand focus:ring-brand/30">
                                        مطلوب
                                    </label>
                                    <button type="button" @click="removeOption(oIndex)" class="icon-btn !size-10 text-muted hover:text-danger" aria-label="حذف الخيار">
                                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                    </button>
                                </div>

                                <div class="mt-3 space-y-2 border-t border-line/60 pt-3">
                                    <template x-for="(value, vIndex) in option.values" :key="vIndex">
                                        <div class="flex items-center gap-2">
                                            <input type="text" :name="'options['+oIndex+'][values]['+vIndex+'][value]'" x-model="value.value" class="input !py-2 !text-xs" placeholder="القيمة، مثال: وسط" maxlength="80">
                                            <div class="flex w-[7rem] shrink-0 items-center justify-start rounded-2xl border border-line bg-white px-2.5 py-2 shadow-[0_1px_1px_rgba(42,28,21,0.03)] transition focus-within:border-brand/60 focus-within:ring-4 focus-within:ring-brand/10">
                                                <input type="number" step="0.001" min="0" :name="'options['+oIndex+'][values]['+vIndex+'][price_delta]'" x-model.number="value.price_delta" class="min-w-[3.5ch] max-w-full border-0 bg-transparent p-0 text-right text-xs font-medium text-ink tabular-nums placeholder:text-muted/55 focus:outline-none focus:ring-0 [appearance:textfield] [&::-webkit-inner-spin-button]:appearance-none [&::-webkit-outer-spin-button]:appearance-none" style="field-sizing: content;" x-effect="$el.style.width = Math.max(3.5, (value.price_delta === null || value.price_delta === '' ? 5.5 : String(value.price_delta).length + 0.5)) + 'ch'" placeholder="0.000">
                                                <span class="pointer-events-none shrink-0 ps-1 text-[9px] text-muted/60">د.أ</span>
                                            </div>
                                            <button type="button" @click="removeValue(option, vIndex)" class="icon-btn !size-9 shrink-0 text-muted hover:text-danger" aria-label="حذف القيمة">
                                                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    </template>

                                    <button type="button" @click="addValue(option)" class="text-[10px] font-bold text-brand transition hover:text-brand-dark">+ إضافة قيمة</button>
                                </div>
                            </div>
                        </template>

                        <div x-show="options.length === 0" class="rounded-2xl border border-dashed border-line bg-canvas/30 px-4 py-8 text-center">
                            <p class="text-[11px] font-bold text-ink-soft">لا توجد خيارات</p>
                            <p class="mt-1 text-[10px] text-muted">هذا المنتج يُطلب كما هو. أضيفي خياراً إن كان له أحجام أو إضافات.</p>
                        </div>
                    </div>

                    @error('options') <p class="field-error">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">
                <div class="card p-5">
                    <h2 class="text-sm font-bold text-ink">النشر</h2>

                    <div class="mt-4 space-y-3">
                        <label class="flex items-start gap-3 rounded-2xl border border-line/70 bg-canvas/40 p-3">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" @checked($activeDefault) class="mt-0.5 size-4 rounded border-line text-brand focus:ring-brand/30">
                            <span>
                                <span class="block text-[11px] font-bold text-ink">متاح في المتجر</span>
                                <span class="mt-0.5 block text-[9px] leading-5 text-muted">أطفئيه لإخفاء المنتج مؤقتاً بدون حذفه.</span>
                            </span>
                        </label>

                        <label class="flex items-start gap-3 rounded-2xl border border-line/70 bg-canvas/40 p-3">
                            <input type="hidden" name="is_featured" value="0">
                            <input type="checkbox" name="is_featured" value="1" @checked($featuredDefault) class="mt-0.5 size-4 rounded border-line text-brand focus:ring-brand/30">
                            <span>
                                <span class="block text-[11px] font-bold text-ink">منتج مميّز</span>
                                <span class="mt-0.5 block text-[9px] leading-5 text-muted">يظهر بشارة «الأكثر طلباً» في واجهة المتجر.</span>
                            </span>
                        </label>
                    </div>
                </div>

                <div class="card sticky top-24 p-5">
                    <button class="btn-primary w-full">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                        {{ $isEdit ? 'حفظ التغييرات' : 'إضافة المنتج' }}
                    </button>
                    <a href="{{ route('products.index') }}" class="btn-ghost mt-2 w-full text-muted">إلغاء</a>

                    @if ($isEdit)
                        <div class="mt-4 border-t border-line/70 pt-4">
                            <p class="text-[10px] font-bold text-muted">منطقة الخطر</p>
                            <button type="submit" form="delete-product-form" class="btn-ghost btn-sm mt-2 w-full !px-2 text-danger hover:bg-danger-soft">
                                حذف المنتج نهائياً
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </form>

    @if ($isEdit)
        <form id="delete-product-form" method="POST" action="{{ route('products.destroy', $product) }}" onsubmit="return confirm('حذف «{{ $product->name }}» من المنيو؟')" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection