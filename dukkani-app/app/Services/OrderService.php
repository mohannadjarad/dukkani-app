<?php

namespace App\Services;

use App\Enums\DepositStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\Customer;
use App\Models\MessageLog;
use App\Models\Order;
use App\Models\Store;
use App\Models\StoreNotification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        protected CartService $cart,
        protected WhatsAppService $whatsapp,
    ) {}

    public function place(Store $store, array $data): Order
    {
        $items = $this->cart->items($store);

        if ($items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'سلة الطلب فارغة. أضف منتجاً أولاً.']);
        }

        $fulfillment = $data['fulfillment_type'] ?? 'delivery';
        $payment = $data['payment_method'] ?? 'cash';
        $subtotal = (int) $items->sum('line_total_fils');
        $deliveryFee = $this->cart->deliveryFee($store, $fulfillment);
        $total = $subtotal + $deliveryFee;
        $deposit = $this->cart->depositFor($store, $total, $payment);

        return DB::transaction(function () use ($store, $data, $items, $fulfillment, $payment, $subtotal, $deliveryFee, $total, $deposit) {
            $customer = Customer::firstOrCreate(
                ['store_id' => $store->id, 'phone' => $data['phone']],
                ['name' => $data['name']]
            );

            $customer->name = $data['name'];

            if ($fulfillment === 'delivery' && ! empty($data['delivery_address'])) {
                $customer->address = $data['delivery_address'];
            }

            $customer->save();

            $scheduledAt = Carbon::parse($data['scheduled_date'].' '.$data['scheduled_time']);

            $order = Order::create([
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'number' => $this->nextNumber($store),
                'status' => OrderStatus::New,
                'fulfillment_type' => $fulfillment,
                'scheduled_at' => $scheduledAt,
                'delivery_address' => $fulfillment === 'delivery' ? ($data['delivery_address'] ?? null) : null,
                'subtotal_fils' => $subtotal,
                'delivery_fee_fils' => $deliveryFee,
                'discount_fils' => 0,
                'total_fils' => $total,
                'deposit_fils' => $deposit,
                'deposit_status' => $deposit > 0 ? DepositStatus::Pending : DepositStatus::None,
                'payment_method' => PaymentMethod::from($payment),
                'balance_due_fils' => max(0, $total - $deposit),
                'customer_notes' => $data['customer_notes'] ?? null,
                'source' => 'storefront',
                'reference_image_path' => $data['reference_image_path'] ?? null,
                'consent_at' => now(),
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'quantity' => $item['quantity'],
                    'unit_price_fils' => $item['unit_price_fils'],
                    'options' => collect($item['options'])->map(fn ($o) => [
                        'name' => $o['name'],
                        'value' => $o['value'],
                        'delta' => $o['delta'],
                    ])->all(),
                    'notes' => $item['notes'],
                    'line_total_fils' => $item['line_total_fils'],
                ]);
            }

            $order->events()->create([
                'type' => 'created',
                'payload' => ['total' => $total, 'items' => $items->sum('quantity')],
            ]);

            StoreNotification::create([
                'store_id' => $store->id,
                'user_id' => $store->owner_id,
                'type' => 'order_created',
                'title' => 'طلب جديد '.$order->number,
                'body' => $order->customer->name.' • '.$order->totalFormatted(),
                'data' => ['order_id' => $order->id],
            ]);

            $this->cart->clear($store);

            return $order;
        });
    }

    public function transition(Order $order, OrderStatus $to, ?User $user = null): Order
    {
        $from = $order->status;

        if (! in_array($to, $from->nextStatuses(), true)) {
            throw ValidationException::withMessages(['status' => 'لا يمكن الانتقال إلى هذه الحالة من الحالة الحالية.']);
        }

        $order->forceFill(['status' => $to])->save();

        $order->events()->create([
            'user_id' => $user?->id,
            'type' => 'status_changed',
            'payload' => ['from' => $from->label(), 'to' => $to->label(), 'from_value' => $from->value, 'to_value' => $to->value],
        ]);

        if (in_array($to, [OrderStatus::Confirmed, OrderStatus::Delivered], true)) {
            StoreNotification::create([
                'store_id' => $order->store_id,
                'user_id' => $order->store->owner_id,
                'type' => $to === OrderStatus::Delivered ? 'order_delivered' : 'order_confirmed',
                'title' => 'الطلب '.$order->number.' '.$to->label(),
                'body' => $order->customer->name.' • '.$order->totalFormatted(),
                'data' => ['order_id' => $order->id],
            ]);
        }

        return $order->refresh();
    }

    public function markDepositPaid(Order $order, ?User $user = null): void
    {
        $this->updateDeposit($order, DepositStatus::Paid, $user);
    }

    public function updateDeposit(Order $order, DepositStatus $status, ?User $user = null): void
    {
        $order->forceFill([
            'deposit_status' => $status,
            'balance_due_fils' => $this->balanceFor($order, $status),
        ])->save();

        $order->events()->create([
            'user_id' => $user?->id,
            'type' => match ($status) {
                DepositStatus::Paid => 'deposit_paid',
                DepositStatus::Refunded => 'deposit_refunded',
                default => 'deposit_updated',
            },
            'payload' => [
                'amount' => $order->deposit_fils,
                'status' => $status->value,
                'label' => $status->label(),
            ],
        ]);
    }

    public function addNote(Order $order, ?string $note, ?User $user = null): void
    {
        $order->forceFill(['internal_notes' => $note])->save();

        $order->events()->create([
            'user_id' => $user?->id,
            'type' => 'note_added',
            'payload' => ['note' => $note],
        ]);
    }

    public function logMessage(Order $order, string $template, ?User $user = null): MessageLog
    {
        $log = MessageLog::create([
            'store_id' => $order->store_id,
            'order_id' => $order->id,
            'channel' => 'whatsapp',
            'template' => $template,
            'to' => $order->customer->phone,
            'status' => 'opened',
            'body' => $this->whatsapp->message($order, $template),
            'sent_at' => now(),
        ]);

        $order->events()->create([
            'user_id' => $user?->id,
            'type' => 'whatsapp_sent',
            'payload' => ['template' => $template],
        ]);

        return $log;
    }

    protected function balanceFor(Order $order, DepositStatus $status): int
    {
        return match ($status) {
            DepositStatus::Paid => max(0, $order->total_fils - $order->deposit_fils),
            DepositStatus::Pending => max(0, $order->total_fils - $order->deposit_fils),
            default => $order->total_fils,
        };
    }

    protected function nextNumber(Store $store): string
    {
        $prefix = 'AB-'.now()->format('ymd').'-';
        $count = Order::forStore($store)->where('number', 'like', $prefix.'%')->count() + 1;

        do {
            $number = $prefix.str_pad((string) $count, 3, '0', STR_PAD_LEFT);
            $count++;
        } while (Order::forStore($store)->where('number', $number)->exists());

        return $number;
    }
}
