<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Services\WebPushService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'url', 'max:700'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
        ]);

        $request->user()->pushSubscriptions()->updateOrCreate(
            ['endpoint' => $data['endpoint']],
            [
                'public_key' => $data['keys']['p256dh'],
                'auth_token' => $data['keys']['auth'],
                'content_encoding' => 'aes128gcm',
            ],
        );

        return response()->json(['ok' => true]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $data = $request->validate([
            'endpoint' => ['required', 'string', 'max:700'],
        ]);

        $request->user()->pushSubscriptions()
            ->where('endpoint', $data['endpoint'])
            ->delete();

        return response()->json(['ok' => true]);
    }

    public function test(Request $request, WebPushService $push): JsonResponse
    {
        $sent = $push->send($request->user(), [
            'title' => 'إشعارات دكّاني مفعّلة',
            'body' => 'رح يوصلك إشعار هون كل ما يجي طلب جديد.',
            'url' => route('dashboard'),
            // A fresh tag each time: some Android builds silently replace a
            // notification that already uses the same tag instead of alerting.
            'tag' => 'push-test-'.now()->timestamp,
        ]);

        return response()->json(['ok' => $sent > 0, 'sent' => $sent]);
    }
}
