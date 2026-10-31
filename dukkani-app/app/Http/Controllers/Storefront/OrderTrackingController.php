<?php

namespace App\Http\Controllers\Storefront;

use App\Enums\FulfillmentType;
use App\Enums\OrderStatus;
use App\Models\Order;
use Illuminate\View\View;

class OrderTrackingController extends StorefrontController
{
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