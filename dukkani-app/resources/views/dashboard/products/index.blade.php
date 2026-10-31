@extends('layouts.dashboard')

@section('title', 'المنتجات')
@section('page-title', 'المنتجات')

@section('content')
    @php
        $queryFor = function (array $overrides = []) {
            $query = array_merge(request()->query(), $overrides);

            return array_filter($query, fn ($value) => $value !== null && $value !== '');
        };

        $tabs = [
            ['key' => 'all', 'label' => 'الكل', 'count' => $stats['total']],
            ['key' => 'active', 'label' => 'متاحة', 'count' => $stats['active']],
            ['key' => 'hidden', 'label' => 'مخفية', 'count' => $stats['hidden']],
        ];
        $filtersActive = $filters['q'] !== '' || $filters['category'] !== 'all' || $filters['status'] !== 'all';
    @endphp

    <div x-data="{ manage: false }">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <h1 class="font-display text-[2rem] font-bold leading-tight text-ink sm:text-[2.35rem]">المنتجات</h1>
                <p class="mt-2 max-w-xl text-sm leading-6 text-muted">
                    أضيفي أصنافك، حدّدي أسعارها وخياراتها، وتحكّمي بما يظهر في متجرك.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button type="button" @click="manage = !manage" class="btn-secondary btn-sm">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m9.75 9.75 4.5 4.5m0-4.5-4.5 4.5M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
                    إدارة التصنيفات
                </button>
                <a href="{{ route('products.create') }}" class="btn-primary btn-sm">
                    <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    منتج جديد
                </a>
            </div>
        </div>

        <div class="mt-6 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="card flex items-center gap-3 p-4">
                <span class="kpi-icon size-10 bg-brand-soft text-brand"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 8-9-5-9 5v8l9 5 9-5V8Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m3.3 8.3 8.7 4.9 8.7-4.9M12 21v-7.8"/></svg></span>
                <div>
                    <p class="text-[11px] font-bold text-muted">إجمالي المنتجات</p>
                    <p class="font-display text-xl font-bold text-ink">{{ $stats['total'] }}</p>
                </div>
            </div>
            <div class="card flex items-center gap-3 p-4">
                <span class="kpi-icon size-10 bg-sage-soft text-sage"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg></span>
                <div>
                    <p class="text-[11px] font-bold text-muted">متاحة في المتجر</p>
                    <p class="font-display text-xl font-bold text-ink">{{ $stats['active'] }}</p>
                </div>
            </div>
            <div class="card flex items-center gap-3 p-4">
                <span class="kpi-icon size-10 bg-canvas text-muted"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg></span>
                <div>
                    <p class="text-[11px] font-bold text-muted">مخفية</p>
                    <p class="font-display text-xl font-bold text-ink">{{ $stats['hidden'] }}</p>
                </div>
            </div>
            <div class="card flex items-center gap-3 p-4">
                <span class="kpi-icon size-10 bg-gold-soft text-[#a47b32]"><svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6Z"/></svg></span>
                <div>
                    <p class="text-[11px] font-bold text-muted">التصنيفات</p>
                    <p class="font-display text-xl font-bold text-ink">{{ $stats['categories'] }}</p>
                </div>
            </div>
        </div>

        {{-- Category manager --}}
        <div x-show="manage" x-transition x-cloak class="card mt-4 p-5">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="text-sm font-bold text-ink">التصنيفات</h2>
                    <p class="mt-0.5 text-[10px] text-muted">حذف تصنيف لا يحذف منتجاته، لكنها ستصبح بدون تصنيف.</p>
                </div>
                <button type="button" @click="manage = false" class="icon-btn !size-8" aria-label="إغلاق">
                    <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.9" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="mt-4 flex flex-wrap gap-2">
                @forelse ($categories as $category)
                    <div class="inline-flex items-center gap-2 rounded-full border border-line bg-white py-1.5 pe-1.5 ps-3.5">
                        <span class="text-[11px] font-bold text-ink-soft">{{ $category->name }}</span>
                        <span class="rounded-full bg-canvas px-2 py-0.5 text-[9px] font-bold text-muted">{{ $category->products_count }}</span>
                        <form method="POST" action="{{ route('products.categories.destroy', $category) }}"
                              onsubmit="return confirm('حذف التصنيف «{{ $category->name }}»؟')">
                            @csrf
                            @method('DELETE')
                            <button class="flex size-6 items-center justify-center rounded-full text-muted transition hover:bg-danger-soft hover:text-danger" aria-label="حذف التصنيف">
                                <svg class="size-3" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                            </button>
                        </form>
                    </div>
                @empty
                    <p class="text-[11px] text-muted">لا توجد تصنيفات بعد — أضيفي الأول أدناه.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('products.categories.store') }}" class="mt-4 flex flex-col gap-2 sm:flex-row sm:items-end">
                @csrf
                <div class="flex-1">
                    <label class="label !mb-1.5 !text-[10px] !text-muted">تصنيف جديد</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="input !py-2.5" placeholder="مثال: حلويات باردة" maxlength="60">
                    @error('name') <p class="field-error">{{ $message }}</p> @enderror
                </div>
                <button class="btn-secondary btn-sm">إضافة التصنيف</button>
            </form>
        </div>

        {{-- Status tabs --}}
        <div class="mt-5 flex gap-2 overflow-x-auto pb-1 no-scrollbar">
            @foreach ($tabs as $tab)
                @php $active = $filters['status'] === $tab['key']; @endphp
                <a href="{{ route('products.index', $queryFor(['status' => $tab['key'], 'page' => null])) }}"
                   class="inline-flex shrink-0 items-center gap-2 rounded-full border px-3.5 py-2 text-[11px] font-bold transition {{ $active ? 'border-brand bg-brand text-white' : 'border-line bg-surface text-ink-soft hover:border-brand/30 hover:text-ink' }}">
                    {{ $tab['label'] }}
                    <span class="rounded-full px-1.5 py-0.5 text-[9px] {{ $active ? 'bg-white/25 text-white' : 'bg-canvas text-muted' }}">{{ $tab['count'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('products.index') }}" class="card mt-4 p-4">
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="min-w-[14rem] flex-1">
                    <label class="label !mb-1.5 !text-[10px] !text-muted">بحث</label>
                    <div class="relative">
                        <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="اسم المنتج أو وصفه..." class="input !py-2.5 !ps-10">
                        <svg class="pointer-events-none absolute inset-y-0 start-3.5 my-auto size-4 text-muted/60" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z"/></svg>
                    </div>
                </div>

                <div class="w-[11rem]">
                    <label class="label !mb-1.5 !text-[10px] !text-muted">التصنيف</label>
                    <select name="category" onchange="this.form.submit()" class="select !py-2.5">
                        <option value="all" @selected($filters['category'] === 'all')>كل التصنيفات</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) $filters['category'] === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="w-[10rem]">
                    <label class="label !mb-1.5 !text-[10px] !text-muted">الترتيب</label>
                    <select name="sort" onchange="this.form.submit()" class="select !py-2.5">
                        <option value="recent" @selected($filters['sort'] === 'recent')>الأحدث</option>
                        <option value="price" @selected($filters['sort'] === 'price')>الأعلى سعراً</option>
                        <option value="name" @selected($filters['sort'] === 'name')>الاسم</option>
                    </select>
                </div>

                <button class="btn-secondary btn-sm">بحث</button>

                @if ($filtersActive)
                    <a href="{{ route('products.index') }}" class="btn-ghost btn-sm text-muted">مسح الفلاتر</a>
                @endif
            </div>
        </form>

        {{-- Grid --}}
        @if ($products->isEmpty())
            <div class="card mt-4 px-6 py-16 text-center">
                <span class="mx-auto flex size-14 items-center justify-center rounded-full bg-canvas text-muted">
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m21 8-9-5-9 5v8l9 5 9-5V8Z"/><path stroke-linecap="round" stroke-linejoin="round" d="m3.3 8.3 8.7 4.9 8.7-4.9M12 21v-7.8"/></svg>
                </span>
                <p class="mt-4 text-sm font-bold text-ink">{{ $filtersActive ? 'لا توجد منتجات مطابقة' : 'منيوك فارغ بعد' }}</p>
                <p class="mt-1 text-[11px] text-muted">{{ $filtersActive ? 'جرّبي تغيير الفلاتر أو امسحيها.' : 'أضيفي أول منتج ليظهر في متجرك.' }}</p>
                <div class="mt-5 flex flex-wrap justify-center gap-2">
                    @if ($filtersActive)
                        <a href="{{ route('products.index') }}" class="btn-secondary btn-sm">مسح الفلاتر</a>
                    @endif
                    <a href="{{ route('products.create') }}" class="btn-primary btn-sm">إضافة منتج</a>
                </div>
            </div>
        @else
            <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($products as $product)
                    <article class="card card-hover group overflow-hidden">
                        <div class="relative aspect-[4/3] overflow-hidden bg-canvas">
                            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}"
                                 class="size-full object-cover transition duration-500 group-hover:scale-[1.03]">

                            <div class="absolute inset-x-0 top-0 flex items-start justify-between gap-2 p-3">
                                <div class="flex flex-col items-start gap-1.5">
                                    @if ($product->is_featured)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-gold-soft/95 px-2.5 py-1 text-[9px] font-bold text-[#8a672c] shadow-sm backdrop-blur">
                                            <span class="size-1.5 rounded-full bg-gold"></span>
                                            مميّز
                                        </span>
                                    @endif
                                    @if (! $product->is_active)
                                        <span class="inline-flex items-center gap-1 rounded-full bg-ink/80 px-2.5 py-1 text-[9px] font-bold text-white shadow-sm backdrop-blur">
                                            مخفي
                                        </span>
                                    @endif
                                </div>

                                <form method="POST" action="{{ route('products.availability', $product) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="flex size-8 items-center justify-center rounded-full bg-white/90 text-ink-soft shadow-sm backdrop-blur transition hover:bg-white" title="{{ $product->is_active ? 'إخفاء من المتجر' : 'إظهار في المتجر' }}" aria-label="تبديل الظهور">
                                        @if ($product->is_active)
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/></svg>
                                        @else
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88"/></svg>
                                        @endif
                                    </button>
                                </form>
                            </div>

                            @unless ($product->is_active)
                                <div class="absolute inset-0 bg-ink/20"></div>
                            @endunless
                        </div>

                        <div class="p-4">
                            <div class="flex items-center gap-1.5 text-[9px] font-bold text-muted">
                                <span>{{ $product->category?->name ?? 'بدون تصنيف' }}</span>
                                <span class="text-line">•</span>
                                <span>{{ $product->options_count }} خيارات</span>
                            </div>

                            <h3 class="mt-1.5 line-clamp-2 text-sm font-bold leading-6 text-ink">{{ $product->name }}</h3>

                            <div class="mt-2.5 flex items-end justify-between gap-2">
                                <div>
                                    <p class="font-display text-lg font-bold text-brand">{{ $product->priceFormatted() }}</p>
                                    <p class="mt-0.5 text-[9px] text-muted">تحضير {{ $product->lead_time_hours ?: $store->lead_time_hours }} ساعة</p>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <a href="{{ route('products.edit', $product) }}" class="btn-secondary btn-sm !px-3 !py-1.5">تعديل</a>
                                    <form method="POST" action="{{ route('products.destroy', $product) }}"
                                          onsubmit="return confirm('حذف «{{ $product->name }}» من المنيو؟')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="icon-btn !size-8 text-muted hover:text-danger" aria-label="حذف المنتج">
                                            <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>

            <x-pagination :paginator="$products" label="منتج" />
        @endif
    </div>
@endsection