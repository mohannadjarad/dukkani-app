<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PushSubscription;
use App\Models\User;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    public function configured(): bool
    {
        return filled(config('webpush.public_key')) && filled(config('webpush.private_key'));
    }

    /**
     * Send one payload to every device the user has enabled. Expired
     * subscriptions are removed so they do not slow down later sends.
     *
     * @param  array{title:string, body:string, url?:string, tag?:string}  $payload
     */
    public function send(User $user, array $payload): int
    {
        if (! $this->configured()) {
            return 0;
        }

        $subscriptions = $user->pushSubscriptions()->get();

        if ($subscriptions->isEmpty()) {
            return 0;
        }

        try {
            $webPush = new WebPush(
                ['VAPID' => [
                    'subject' => (string) config('webpush.subject'),
                    'publicKey' => (string) config('webpush.public_key'),
                    'privateKey' => (string) config('webpush.private_key'),
                ]],
                ['TTL' => 86400, 'urgency' => 'high'],
                new Client(['timeout' => 10, 'connect_timeout' => 5]),
            );

            $encoded = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

            foreach ($subscriptions as $subscription) {
                $webPush->queueNotification(
                    new Subscription(
                        $subscription->endpoint,
                        $subscription->public_key,
                        $subscription->auth_token,
                        $subscription->content_encoding,
                    ),
                    $encoded,
                );
            }

            $sent = 0;

            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    $sent++;

                    continue;
                }

                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint', $report->getEndpoint())->delete();
                }

                Log::info('Web push was not delivered.', [
                    'endpoint' => $report->getEndpoint(),
                    'reason' => $report->getReason(),
                ]);
            }

            return $sent;
        } catch (\Throwable $e) {
            Log::warning('Web push could not be sent.', ['error' => $e->getMessage()]);

            return 0;
        }
    }

    public function notifyNewOrder(Order $order): void
    {
        $order->loadMissing(['store.owner', 'customer']);

        $owner = $order->store?->owner;

        if (! $owner) {
            return;
        }

        $this->send($owner, [
            'title' => 'طلب جديد '.$order->number,
            'body' => $order->customer->name.' • '.$order->totalFormatted(),
            'url' => route('orders.show', $order),
            'tag' => 'order-'.$order->id,
        ]);
    }
}
