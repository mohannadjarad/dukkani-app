@extends('layouts.dashboard')

@section('title', $customer->name)
@section('page-title', 'ملف العميل')

@section('content')
    @php
        use App\Support\Money;
    @endphp

    <nav class="flex items-center gap-1.5 text-[10px] font-medium text-muted/70">
        <a href="{{ route('customers.index') }}" class="transition hover:text-brand">العملاء</a>
        <svg class="size-3 text-muted/40" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m15 19.5-7.5-7.5L15 4.5"/></svg>
        <span class="text-ink-soft">{{ $customer->name }}</span>
    </nav>

    <div class="mt-4 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div class="flex items-center gap-4">
            <x-avatar :name="$customer->name" size="lg" class="!size-16 !text-lg" />
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="font-display text-2xl font-bold text-ink">{{ $customer->name }}</h1>
                    @if ($customer->orders_count > 1 || $customer->orders->count() > 1)
                        <span class="rounded-full bg-brand-soft px-2.5 py-1 text-[9px] font-bold text-brand-dark">عميل متكرر</span>
                    @endif
                </div>
                <p class="mt-1 text-xs text-muted" dir="ltr">{{ $customer->phone }}</p>
                @if (! empty($customer->tags))
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach ($customer->tags as $tag)
                            <span class="rounded-full border border-line bg-surface px-2.5 py-1 text-[9px] font-bold text-muted">{{ $tag }}</span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="tel:{{ preg_replace('/\s+/', '', $customer->phone) }}" class="btn-secondary btn-sm">
                <svg class="size-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z"/></svg>
                اتصال
            </a>
            @if ($wa = $customer->whatsappLink('مرحباً '.$customer->name.' 👋'))
                <a href="{{ $wa }}" target="_blank" rel="noopener" class="btn-secondary btn-sm">
                    <svg class="size-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                    واتساب
                </a>
            @endif
        </div>
    </div>

    <div class="mt-6 grid gap-4 lg:grid-cols-[1.35fr_0.65fr] lg:items-start">
        <div class="space-y-4">
            <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                <div class="card p-4">
                    <p class="text-[10px] font-bold text-muted">عدد الطلبات</p>
                    <p class="mt-1 font-display text-xl font-bold text-ink">{{ $stats['orders'] }}</p>
                </div>
                <div class="card p-4">
                    <p class="text-[10px] font-bold text-muted">طلبات مكتملة</p>
                    <p class="mt-1 font-display text-xl font-bold text-ink">{{ $stats['delivered'] }}</p>
                </div>
                <div class="card p-4">
                    <p class="text-[10px] font-bold text-muted">إجمالي الشراء</p>
                    <p class="mt-1 font-display text-xl font-bold text-brand">{{ Money::format($stats['spent']) }}</p>
                </div>
                <div class="card p-4">
                    <p class="text-[10px] font-bold text-muted">متوسط الطلب</p>
                    <p class="mt-1 font-display text-xl font-bold text-ink">{{ Money::format($stats['average']) }}</p>
                </div>
            </div>

            <div class="card overflow-hidden">
                <div class="flex items-center justify-between border-b border-line/70 px-5 py-4">
                    <h2 class="text-sm font-bold text-ink">سجل الطلبات</h2>
                    <span class="text-[9px] text-muted/60">{{ $stats['orders'] }} طلب</span>
                </div>

                @forelse ($orders as $order)
                    <a href="{{ route('orders.show', $order) }}" class="flex items-center gap-3.5 border-b border-line/50 px-5 py-4 transition last:border-0 hover:bg-canvas/50">
                        <div class="flex w-12 shrink-0 flex-col items-center rounded-xl bg-canvas py-2">
                            <span class="text-[11px] font-bold text-ink">{{ $order->scheduled_at->translatedFormat('j') }}</span>
                            <span class="text-[9px] text-muted">{{ $order->scheduled_at->translatedFormat('M') }}</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="font-mono text-[10px] font-bold text-brand" dir="ltr">{{ $order->number }}</span>
                                <x-status-badge :status="$order->status" />
                            </div>
                            <p class="mt-1 truncate text-[10px] text-muted">{{ $order->itemsSummary() }}</p>
                        </div>
                        <div class="shrink-0 text-end">
                            <p class="text-xs font-bold text-ink">{{ $order->totalFormatted() }}</p>
                            <p class="mt-0.5 text-[9px] text-muted/70">{{ $order->fulfillment_type->label() }}</p>
                        </div>
                    </a>
                @empty
                    <div class="px-6 py-12 text-center">
                        <p class="text-xs font-bold text-ink">لا توجد طلبات بعد</p>
                        <p class="mt-1 text-[10px] text-muted">سيظهر هنا كل طلب يقدّمه هذا العميل.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            <div class="card p-5 sm:p-6">
                <h2 class="text-sm font-bold text-ink">بيانات العميل</h2>
                <p class="mt-1 text-[10px] text-muted">الوسوم والملاحظات تساعدك على تذكّر أفضليات العميل.</p>

                <form method="POST" action="{{ route('customers.update', $customer) }}" class="mt-4 space-y-3.5">
                    @csrf
                    @method('PATCH')

                    <div>
                        <label class="label !mb-1.5 !text-[10px] !text-muted">الاسم</label>
                        <input type="text" name="name" value="{{ old('name', $customer->name) }}" class="input !py-2.5" maxlength="80" required>
                        @error('name') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label !mb-1.5 !text-[10px] !text-muted">الهاتف</label>
                        <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}" class="input !py-2.5" dir="ltr" maxlength="20" required>
                        @error('phone') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label !mb-1.5 !text-[10px] !text-muted">البريد الإلكتروني</label>
                        <input type="email" name="email" value="{{ old('email', $customer->email) }}" class="input !py-2.5" dir="ltr" placeholder="name@example.com" maxlength="120">
                        @error('email') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label !mb-1.5 !text-[10px] !text-muted">العنوان</label>
                        <input type="text" name="address" value="{{ old('address', $customer->address) }}" class="input !py-2.5" placeholder="المنطقة، الشارع..." maxlength="300">
                        @error('address') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <div x-data="{ tags: @js(implode('، ', old('tags') ? explode(',', old('tags')) : ($customer->tags ?? []))) }">
                        <label class="label !mb-1.5 !text-[10px] !text-muted">الوسوم</label>
                        <input type="text" name="tags" x-model="tags" class="input !py-2.5" placeholder="vip، متكرر، شركات" maxlength="300">
                        @error('tags') <p class="field-error">{{ $message }}</p> @enderror
                        @if ($allTags->isNotEmpty())
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                @foreach ($allTags as $tag)
                                    <button type="button" @click="tags = tags.trim() ? (tags.includes('{{ $tag }}') ? tags : tags.replace(/[،,]\s*$/, '') + '، ' + '{{ $tag }}') : '{{ $tag }}'"
                                            class="rounded-full border border-line bg-surface px-2.5 py-1 text-[9px] font-bold text-muted transition hover:border-brand/30 hover:text-brand">
                                        + {{ $tag }}
                                    </button>
                                @endforeach
                            </div>
                        @endif
                        <p class="mt-1.5 text-[9px] text-muted/70">افصلي بين الوسوم بفاصلة.</p>
                    </div>

                    <div>
                        <label class="label !mb-1.5 !text-[10px] !text-muted">ملاحظات</label>
                        <textarea name="notes" rows="3" class="textarea !min-h-20" maxlength="1000" placeholder="تفضيلات، حساسية، مناسبات...">{{ old('notes', $customer->notes) }}</textarea>
                        @error('notes') <p class="field-error">{{ $message }}</p> @enderror
                    </div>

                    <button class="btn-primary w-full">حفظ بيانات العميل</button>
                </form>
            </div>

            <div class="card p-5">
                <h2 class="text-sm font-bold text-ink">لمحة سريعة</h2>
                <dl class="mt-3 space-y-2.5 text-[11px]">
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">أول طلب</dt>
                        <dd class="font-bold text-ink-soft">{{ $stats['first'] ? \Illuminate\Support\Carbon::parse($stats['first'])->translatedFormat('j M Y') : '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">آخر طلب</dt>
                        <dd class="font-bold text-ink-soft">{{ $stats['last'] ? \Illuminate\Support\Carbon::parse($stats['last'])->translatedFormat('j M Y') : '—' }}</dd>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <dt class="text-muted">عميل منذ</dt>
                        <dd class="font-bold text-ink-soft">{{ $customer->created_at->translatedFormat('j M Y') }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
@endsection