<?php

namespace App\Services;

use App\Models\Order;
use App\Support\Money;

class WhatsAppService
{
    public const TEMPLATES = [
        'order_confirmation' => 'تأكيد الطلب',
        'order_ready' => 'الطلب جاهز',
        'out_for_delivery' => 'في الطريق',
        'delivered' => 'شكر وتقييم',
        'deposit_reminder' => 'تذكير بالعربون',
        'follow_up' => 'متابعة العميل',
    ];

    public function message(Order $order, string $template): string
    {
        $store = $order->store;
        $customer = $order->customer;
        $date = $order->scheduled_at->translatedFormat('l j F');
        $time = $order->scheduled_at->format('g:i A');
        $fulfillment = $order->fulfillment_type->label();
        $tracking = $order->publicUrl();

        $body = match ($template) {
            'order_confirmation' => "مرحباً {$customer->name} 🌸\nتم استلام طلبك {$order->number} في {$store->name}.\n\nالموعد: {$date} — {$time}\nطريقة الاستلام: {$fulfillment}\nالإجمالي: {$order->totalFormatted()}".($order->deposit_fils > 0 ? "\nالعربون المطلوب: {$order->depositFormatted()} (تحويل CliQ)" : '')."\n\nتابع طلبك من هنا:\n{$tracking}\n\nشكراً لثقتك بنا 🤍",
            'order_ready' => "مرحباً {$customer->name} 🤍\nخبر حلو: طلبك {$order->number} صار جاهز!\n\n{$fulfillment}: {$date} — {$time}".($order->fulfillment_type->value === 'delivery' ? "\nعنوان التوصيل: {$order->delivery_address}" : "\nعنوان الاستلام: {$store->address}، {$store->city}")."\n\nرابط المتابعة:\n{$tracking}",
            'out_for_delivery' => "مرحباً {$customer->name} 🚗\nطلبك {$order->number} في الطريق إليك الآن.\n\nالعنوان: {$order->delivery_address}\nالمبلغ المتبقي: {$order->balanceFormatted()}",
            'delivered' => "مرحباً {$customer->name} 🤍\nوصل طلبك {$order->number}؟ نتمنى أنه يعجبك!\n\nيسعدنا تقييمك، ولو حابب تطلب مرة ثانية احنا جاهزين.\n{$store->name}",
            'deposit_reminder' => "مرحباً {$customer->name} 🌸\nتذكير لطيف بعربون الطلب {$order->number}.\n\nقيمة العربون: {$order->depositFormatted()}\nالمتبقي: {$order->balanceFormatted()}\n\n{$store->deposit_instructions}\n\nبعد تأكيد التحويل نبدأ التحضير فوراً 🤍",
            'follow_up' => "مرحباً {$customer->name} 🤍\nشكراً لطلبك من {$store->name}!\n\nعندك مناسبة قريبة أو فكرة تصميم؟ رد على هذه الرسالة وبنفرح نساعدك.\n\n{$store->name}",
            default => "مرحباً {$customer->name} 🤍",
        };

        return $body;
    }

    public function link(Order $order, string $template = 'order_confirmation'): string
    {
        // The seller is messaging the customer, so the link targets the
        // customer's number, not the store's own WhatsApp number.
        return $order->customer->whatsappLink($this->message($order, $template))
            ?? $order->store->whatsappLink($this->message($order, $template));
    }

    public function summary(Order $order, string $template): string
    {
        return 'إجمالي '.Money::format($order->total_fils).' • '.($template === 'deposit_reminder' ? 'عربون '.Money::format($order->deposit_fils) : $order->fulfillment_type->label());
    }
}
