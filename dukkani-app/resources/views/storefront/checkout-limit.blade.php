@extends('layouts.storefront')

@section('title', 'المتجر وصل حده الشهري')

@section('content')
    @php
        $storeLink = $store->whatsappLink($storeMessage);
        $supportLink = filled($supportWhatsapp)
            ? \App\Support\Phone::whatsappLink(
                $supportWhatsapp,
                'مرحباً، متجر "'.$store->name.'" وصل حده الشهري وأنا عميل عنده وبدي أطلب.',
                $store->dialCode()
            )
            : null;
    @endphp

    <section class="mx-auto max-w-3xl px-5 py-14 sm:px-8 sm:py-20">
        <div class="card overflow-hidden p-6 text-center sm:p-10">
            <span class="mx-auto flex size-16 items-center justify-center rounded-3xl bg-gold-soft text-[#a47b32]">
                <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="1.6" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/></svg>
            </span>

            <h1 class="mt-5 font-display text-2xl font-bold leading-tight text-ink sm:text-3xl">المتجر وصل حده الشهري 🙏</h1>
            <p class="mx-auto mt-3 max-w-lg text-sm leading-7 text-muted">
                نعتذر، {{ $store->name }} استقبل الحد الأقصى من الطلبات لهذا الشهر. لكن طلبك يهمنا —
                تقدري تطلبي مباشرة عبر واتساب المتجر، أو تتواصلي مع الدعم لإكمال الطلب.
            </p>

            @if ($items->isNotEmpty())
                <div class="mx-auto mt-5 max-w-md rounded-2xl border border-line/70 bg-canvas/50 p-4 text-start">
                    <p class="text-[11px] font-bold text-ink">سلتك الحالية</p>
                    <ul class="mt-2 space-y-1.5 text-[11px] text-ink-soft">
                        @foreach ($items as $item)
                            <li class="flex items-center justify-between gap-3">
                                <span class="min-w-0 truncate">{{ $item['product']->name }}</span>
                                <span class="shrink-0 text-muted">×{{ $item['quantity'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-7 flex flex-col items-stretch justify-center gap-2.5 sm:flex-row">
                @if ($storeLink)
                    <a href="{{ $storeLink }}" target="_blank" rel="noopener" class="btn-primary">
                        <svg class="size-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413Z"/></svg>
                        اطلبي عبر واتساب المتجر
                    </a>
                @endif

                @if ($supportLink)
                    <a href="{{ $supportLink }}" target="_blank" rel="noopener" class="btn-secondary">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 8.511c.884.284 1.5 1.128 1.5 2.097v4.286c0 1.136-.847 2.1-1.98 2.193-.34.027-.68.052-1.02.072v3.091l-3-3c-1.354 0-2.694-.055-4.02-.163a2.115 2.115 0 0 1-.825-.242m9.345-8.334a2.126 2.126 0 0 0-.476-.095 48.64 48.64 0 0 0-8.048 0c-1.131.094-1.976 1.057-1.976 2.192v4.286c0 .837.46 1.58 1.155 1.951m9.345-8.334V6.637c0-1.621-1.152-3.026-2.76-3.235A48.455 48.455 0 0 0 11.25 3c-2.115 0-4.198.137-6.24.402-1.608.209-2.76 1.614-2.76 3.235v6.226c0 1.621 1.152 3.026 2.76 3.235.577.075 1.157.14 1.74.194V21l4.155-4.155"/></svg>
                        تواصلي مع {{ $supportName }}
                    </a>
                @endif
            </div>

            <a href="{{ route('storefront.menu', $store) }}" class="mt-5 inline-block text-[11px] font-bold text-muted transition hover:text-brand">العودة إلى المنيو</a>
        </div>
    </section>
@endsection