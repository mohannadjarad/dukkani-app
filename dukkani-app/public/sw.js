/* Service worker for Web Push. Keep it dependency-free: iOS only keeps a
   worker that responds quickly to push and notificationclick events. */

const FALLBACK = {
    title: 'طلب جديد على دكّاني',
    body: 'وصلك طلب جديد — افتح اللوحة للمراجعة.',
    url: '/dashboard/orders',
    tag: 'dukkani-order',
};

self.addEventListener('install', () => {
    self.skipWaiting();
});

self.addEventListener('activate', (event) => {
    event.waitUntil(self.clients.claim());
});

self.addEventListener('push', (event) => {
    let payload = {};

    try {
        payload = event.data ? event.data.json() : {};
    } catch {
        payload = { body: event.data ? event.data.text() : '' };
    }

    const title = payload.title || FALLBACK.title;
    const options = {
        body: payload.body || FALLBACK.body,
        icon: '/pwa/icon-192.png',
        badge: '/pwa/icon-192.png',
        dir: 'rtl',
        lang: 'ar',
        tag: payload.tag || FALLBACK.tag,
        renotify: true,
        data: { url: payload.url || FALLBACK.url },
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const target = new URL(event.notification.data?.url || FALLBACK.url, self.location.origin).href;

    event.waitUntil((async () => {
        const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });

        for (const client of windows) {
            if (client.url.startsWith(self.location.origin)) {
                await client.focus();

                if ('navigate' in client) {
                    await client.navigate(target);
                }

                return;
            }
        }

        await self.clients.openWindow(target);
    })());
});