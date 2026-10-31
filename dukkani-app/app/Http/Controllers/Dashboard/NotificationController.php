<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\StoreNotification;
use Illuminate\Http\RedirectResponse;

class NotificationController extends Controller
{
    public function markAllRead(): RedirectResponse
    {
        $store = auth()->user()->store()->firstOrFail();

        StoreNotification::where('store_id', $store->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('status', 'تم تعليم كل الإشعارات كمقروءة.');
    }

    public function markRead(StoreNotification $notification): RedirectResponse
    {
        $store = auth()->user()->store()->firstOrFail();

        abort_unless($notification->store_id === $store->id, 404);

        $notification->markAsRead();

        $orderId = $notification->data['order_id'] ?? null;

        if ($orderId && $store->orders()->whereKey($orderId)->exists()) {
            return redirect()->route('orders.show', $orderId);
        }

        return back();
    }
}
