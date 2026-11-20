/* Service worker for Web Push. Keep it dependency-free: iOS only keeps a
   worker that responds quickly to push and notificationclick events. */

const ICON = '/pwa/icon-192.png';
const BADGE = '/pwa/badge.png';

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

function bodyOf(payload) {
    return payload.body || FALLBACK.body;
}

function urlOf(payload) {
    return payload.url || FALLBACK.url;
}

/* Chrome 101 on Android rejects a few modern options instead of ignoring
   them, and a rejected showNotification means the owner sees nothing. Keep a
   conservative set for the retry. */
function minimalOptions(payload) {
    return {
        body: bodyOf(payload),
        icon: ICON,
        tag: payload.tag || FALLBACK.tag,
        data: { url: urlOf(payload) },
    };
}

self.addEventListener('push', (event) => {
    let payload = {};

    try {
        payload = event.data ? event.data.json() : {};
    } catch {
        payload = { body: event.data ? event.data.text() : '' };
    }

    const title = payload.title || FALLBACK.title;

    event.waitUntil((async () => {
        try {
            await self.registration.showNotification(title, {
                body: bodyOf(payload),
                icon: ICON,
                badge: BADGE,
                dir: 'rtl',
                lang: 'ar',
                tag: payload.tag || FALLBACK.tag,
                renotify: true,
                vibrate: [120, 60, 120],
                data: { url: urlOf(payload) },
            });
        } catch {
            try {
                await self.registration.showNotification(title, minimalOptions(payload));
            } catch (error) {
                // Nothing else to try: report it so it is visible in the
                // worker console instead of failing silently.
                console.error('Dukkani push notification failed', error);
            }
        }
    })());
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