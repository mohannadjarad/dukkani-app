<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Store;
use App\Support\Phone;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderTrackingController extends StorefrontController
{
    /**
     * Lets a customer return to her orders without the original link. The
     * phone number used at checkout is the only thing she has to remember.
     */
    public function lookup(Store $store): View
    {
        $ids = session()->pull('tracking_matches', []);

        $orders = $ids
            ? Order::forStore($store)->whereIn('id', $ids)->orderByDesc('created_at')->get()
            : collect();

        return $this->render($store, 'storefront.track-lookup', [
            'orders' => $orders,
        ]);
    }

    public function find(Request $request, Store $store): RedirectResponse
    {
        $data = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
        ], [
            'phone.required' => 'أدخلي رقم الهاتف اللي طلبتِ فيه.',
            'phone.max' => 'رقم الهاتف طويل كتير.',
        ]);

        $normalized = Phone::normalize($data['phone'], $store->dialCode());

        if (! $normalized) {
            return back()->withErrors(['phone' => 'رقم الهاتف غير صحيح.'])->withInput();
        }

        // Numbers may be stored with or without the country code, so narrow the
        // query by the last 7 digits and compare the full normalized value here.
        $tail = substr($normalized, -7);

        $customers = $store->customers()
            ->where('phone', 'like', '%'.$tail.'%')
            ->get()
            ->filter(fn ($customer) => Phone::normalize($customer->phone, $store->dialCode()) === $normalized);

        if ($customers->isEmpty()) {
            return back()
                ->withErrors(['phone' => 'ما لقينا طلبات بهذا الرقم. تأكدي من الرقم أو راسلي المتجر.'])
                ->withInput();
        }

        $orders = Order::forStore($store)
            ->whereIn('customer_id', $customers->pluck('id'))
            ->orderByDesc('created_at')
            ->take(20)
            ->get();

        if ($orders->isEmpty()) {
            return back()
                ->withErrors(['phone' => 'ما لقينا طلبات بهذا الرقم. تأكدي من الرقم أو راسلي المتجر.'])
                ->withInput();
        }

        if ($orders->count() === 1) {
            return redirect()->route('track.show', $orders->first()->public_token);
        }

        session()->put('tracking_matches', $orders->pluck('id')->all());

        return redirect()->route('storefront.track.lookup', $store);
    }

    public function show(Order $order): View
    {
        $order->load(['store.hours', 'customer', 'items', 'events']);

        $store = $order->store;

        $steps = [
            OrderStatus::New,
            OrderStatus::Confirmed,
            OrderStatus::InProduction,
            OrderStatus::Ready,
        ];

        if ($order->fulfillment_type === FulfillmentType::Delivery) {
            $steps[] = OrderStatus::OutForDelivery;
        }

        $steps[] = OrderStatus::Delivered;

        $eventTimes = [];

        foreach ($order->events as $event) {
            $to = $event->payload['to_value'] ?? null;

            if ($event->type === 'status_changed' && $to) {
                $eventTimes[$to] = $event->created_at;
            }
        }

        $eventTimes[OrderStatus::New->value] = $order->created_at;

        $cancelled = $order->status === OrderStatus::Cancelled;

        $phone = $order->customer->phone;
        $maskedPhone = mb_strlen($phone) > 6
            ? mb_substr($phone, 0, 3).'•••'.mb_substr($phone, -3)
            : $phone;

        return $this->render($store, 'storefront.track', [
            'order' => $order,
            'steps' => $steps,
            'eventTimes' => $eventTimes,
            'cancelled' => $cancelled,
            'cancelledAt' => $cancelled ? ($eventTimes[OrderStatus::Cancelled->value] ?? null) : null,
            'maskedPhone' => $maskedPhone,
            'trackingUrl' => $order->publicUrl(),
            'whatsappLink' => $store->whatsappLink(
                "مرحباً، عندي سؤال بخصوص الطلب {$order->number}.".($order->scheduled_at ? "\nالموعد: ".$order->scheduled_at->translatedFormat('j F، g:i A') : '')
            ),
        ]);
    }
}