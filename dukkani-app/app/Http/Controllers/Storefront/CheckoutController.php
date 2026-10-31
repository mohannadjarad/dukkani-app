<?php

namespace App\Http\Controllers\Storefront;

use App\Models\Store;
use App\Services\OrderService;
use App\Services\ScheduleService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends StorefrontController
{
    public function create(Store $store, ScheduleService $schedule): View|RedirectResponse
    {
        $items = $this->cart->items($store);

        if ($items->isEmpty()) {
            return redirect()
                ->route('storefront.menu', $store)
                ->with('status', 'سلتك فارغة. اختاري شيئاً حلوًا أولاً.');
        }

        $store->load('hours');
        [$defaultDate, $defaultTime] = $schedule->defaults($store);

        $subtotal = (int) $items->sum('line_total_fils');
        $fulfillment = old('fulfillment_type', $store->delivery_enabled ? 'delivery' : 'pickup');

        $payload = $schedule->payload($store);
        $checkoutConfig = array_merge($payload, [
                'fulfillment' => $fulfillment,
                'payment' => old('payment_method', 'cash'),
                'date' => old('scheduled_date', $defaultDate),
                'time' => old('scheduled_time', $defaultTime),
                'subtotal' => $subtotal,
                'deliveryFee' => $store->delivery_enabled ? (int) $store->delivery_fee_fils : 0,
                'depositPercent' => (int) $store->deposit_percent,
                'deliveryEnabled' => (bool) $store->delivery_enabled,
                'pickupEnabled' => (bool) $store->pickup_enabled,
                'name' => old('name'),
                'phone' => old('phone'),
                'address' => old('delivery_address'),
                'notes' => old('customer_notes'),
            ]);

        return $this->render($store, 'storefront.checkout', [
            'items' => $items,
            'subtotal' => $subtotal,
            'defaultDate' => $defaultDate,
            'defaultTime' => $defaultTime,
            'schedule' => $payload,
            'checkoutConfig' => $checkoutConfig,
        ]);
    }

    public function store(Request $request, Store $store, ScheduleService $schedule, OrderService $orders): RedirectResponse
    {
        if ($this->cart->isEmpty($store)) {
            return redirect()
                ->route('storefront.menu', $store)
                ->with('status', 'سلتك فارغة. اختاري شيئاً حلوًا أولاً.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'phone' => ['required', 'string', 'min:7', 'max:20', 'regex:/^[0-9+\s\-()]+$/'],
            'fulfillment_type' => ['required', Rule::in(['delivery', 'pickup'])],
            'delivery_address' => ['nullable', 'required_if:fulfillment_type,delivery', 'string', 'min:8', 'max:300'],
            'scheduled_date' => ['required', 'date_format:Y-m-d'],
            'scheduled_time' => ['required', 'date_format:H:i'],
            'payment_method' => ['required', Rule::in(['cash', 'cliq', 'bank_transfer'])],
            'customer_notes' => ['nullable', 'string', 'max:500'],
            'consent' => ['accepted'],
        ], [
            'name.required' => 'اكتبي اسمك حتى نعرف لمن نجهّز الطلب.',
            'name.min' => 'الاسم قصير جداً.',
            'phone.required' => 'رقم الهاتف مطلوب للتواصل وتأكيد الطلب.',
            'phone.regex' => 'رقم الهاتف يحتوي رموزاً غير مسموحة.',
            'fulfillment_type.required' => 'اختاري طريقة الاستلام.',
            'fulfillment_type.in' => 'طريقة الاستلام غير متاحة.',
            'delivery_address.required_if' => 'عنوان التوصيل مطلوب عند اختيار التوصيل.',
            'delivery_address.min' => 'اكتبي عنواناً أوضح (المنطقة، الشارع، رقم البناية).',
            'scheduled_date.required' => 'اختاري تاريخ الاستلام أو التوصيل.',
            'scheduled_date.date_format' => 'صيغة التاريخ غير صحيحة.',
            'scheduled_time.required' => 'اختاري وقت الاستلام أو التوصيل.',
            'scheduled_time.date_format' => 'صيغة الوقت غير صحيحة.',
            'payment_method.required' => 'اختاري طريقة الدفع.',
            'payment_method.in' => 'طريقة الدفع غير متاحة.',
            'customer_notes.max' => 'الملاحظات طويلة جداً، اختصريها قليلاً.',
            'consent.accepted' => 'نحتاج موافقتك على استخدام بياناتك لإتمام الطلب.',
        ]);

        if ($data['fulfillment_type'] === 'delivery' && ! $store->delivery_enabled) {
            throw ValidationException::withMessages([
                'fulfillment_type' => 'التوصيل غير متاح حالياً لهذا المتجر.',
            ]);
        }

        if ($data['fulfillment_type'] === 'pickup' && ! $store->pickup_enabled) {
            throw ValidationException::withMessages([
                'fulfillment_type' => 'الاستلام من المتجر غير متاح حالياً.',
            ]);
        }

        $schedule->assertValid($store, $data['scheduled_date'], $data['scheduled_time']);

        $order = $orders->place($store, $data);

        return redirect()
            ->route('track.show', $order->public_token)
            ->with('status', 'وصلنا طلبك! احتفظي برابط المتابعة لمتابعة التحضير.');
    }
}