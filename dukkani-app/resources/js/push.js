// Owner-facing Web Push. The layout only renders the VAPID meta tag when the
// server has keys, so every function here stays inert on the storefront.

const $ = (selector) => document.querySelector(selector);

function meta(name) {
    return document.querySelector(`meta[name="${name}"]`)?.content?.trim() || '';
}

function csrfToken() {
    return meta('csrf-token');
}

function applicationServerKey() {
    const value = meta('vapid-public-key');

    if (!value) {
        return null;
    }

    const padding = '='.repeat((4 - (value.length % 4)) % 4);
    const base64 = (value + padding).replace(/-/g, '+').replace(/_/g, '/');

    try {
        const raw = window.atob(base64);
        const bytes = new Uint8Array(raw.length);

        for (let i = 0; i < raw.length; i += 1) {
            bytes[i] = raw.charCodeAt(i);
        }

        return bytes;
    } catch {
        return null;
    }
}

async function api(path, body) {
    const controller = new AbortController();
    const timer = window.setTimeout(() => controller.abort(), 20000);

    try {
        const response = await fetch(path, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify(body || {}),
            signal: controller.signal,
        });

        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
            throw new Error(data.message || 'تعذّر الاتصال بالخادم.');
        }

        return data;
    } catch (error) {
        if (error.name === 'AbortError') {
            throw new Error('انتهت مهلة الاتصال. تأكد من الإنترنت وجرّب مرة ثانية.');
        }

        throw error;
    } finally {
        window.clearTimeout(timer);
    }
}

function withTimeout(promise, ms) {
    return new Promise((resolve, reject) => {
        const timer = window.setTimeout(
            () => reject(new Error('انتهت مهلة تفعيل الإشعارات. جرّب مرة ثانية.')),
            ms,
        );

        promise.then(
            (value) => {
                window.clearTimeout(timer);
                resolve(value);
            },
            (error) => {
                window.clearTimeout(timer);
                reject(error);
            },
        );
    });
}

function flash(message) {
    const toast = $('#push-toast');

    if (!toast) {
        return;
    }

    toast.querySelector('p').textContent = message;
    toast.classList.remove('hidden');
    toast.classList.add('flex');

    window.clearTimeout(flash.timer);
    // Longer instructions need more time to read than a one-line confirmation.
    const duration = Math.min(12000, 4200 + message.length * 70);
    flash.timer = window.setTimeout(() => {
        toast.classList.add('hidden');
        toast.classList.remove('flex');
    }, duration);
}

let state = 'off';
let busy = false;

const PROMPT_COOLDOWN = 3 * 24 * 60 * 60 * 1000;

function hidePrompt() {
    const prompt = $('#push-prompt');

    if (!prompt) {
        return;
    }

    prompt.classList.add('hidden');
    prompt.classList.remove('flex');
    document.body.classList.remove('overflow-hidden');
}

function closePrompt(remember = true) {
    hidePrompt();

    if (remember) {
        window.localStorage.setItem('dukkani.push.promptedAt', String(Date.now()));
    }
}

function promptWasDismissed() {
    const at = Number(window.localStorage.getItem('dukkani.push.promptedAt') || 0);

    return at > 0 && Date.now() - at < PROMPT_COOLDOWN;
}

function openPrompt() {
    const prompt = $('#push-prompt');

    if (!prompt) {
        return;
    }

    prompt.classList.remove('hidden');
    prompt.classList.add('flex');
    document.body.classList.add('overflow-hidden');
}

function setState(next) {
    state = next;
    document.documentElement.dataset.pushState = next;

    if (next !== 'off') {
        hidePrompt();
    }

    const toggle = $('#push-toggle');
    const dot = $('#push-dot');
    const banner = $('#push-banner');
    const enable = $('#push-enable');
    const disable = $('#push-disable');
    const diagnose = $('#push-diagnose');
    const note = $('#push-banner-text');

    if (next === 'unsupported') {
        toggle?.classList.add('hidden');
        banner?.classList.add('hidden');
        banner?.classList.remove('flex');
        disable?.classList.add('hidden');
        diagnose?.classList.add('hidden');
        diagnose?.classList.remove('inline-flex');

        return;
    }

    if (toggle) {
        toggle.classList.remove('hidden');
        toggle.setAttribute('aria-pressed', next === 'on' ? 'true' : 'false');
        toggle.title = next === 'on'
            ? 'إشعارات الطلبات مفعّلة — اضغط لتجربة إشعار'
            : next === 'blocked'
                ? 'الإشعارات محظورة من إعدادات المتصفح'
                : 'تفعيل إشعارات الطلبات';
    }

    dot?.classList.toggle('hidden', next !== 'off');

    if (disable) {
        disable.classList.toggle('hidden', next !== 'on');
        disable.classList.toggle('inline-flex', next === 'on');
    }

    if (diagnose) {
        diagnose.classList.remove('hidden');
        diagnose.classList.add('inline-flex');
    }

    if (!banner) {
        return;
    }

    const dismissed = window.localStorage.getItem('dukkani.push.dismissed') === '1';
    const visible = (next === 'off' || next === 'blocked') && !dismissed;

    banner.classList.toggle('hidden', !visible);
    banner.classList.toggle('flex', visible);

    if (enable) {
        enable.classList.toggle('hidden', next === 'blocked');
    }

    if (note) {
        note.textContent = next === 'blocked'
            ? 'الإشعارات محظورة من إعدادات المتصفح. افتح إعدادات الموقع واسمح بالإشعارات، وبعدها جرّب مرة ثانية.'
            : 'فعّل الإشعارات مرة واحدة، ورح يوصلك تنبيه كل ما يجي طلب جديد حتى لو اللوحة مسكّرة.';
    }
}

function setBusy(value) {
    busy = value;

    document.querySelectorAll('#push-enable, #push-toggle, #push-diagnose, #push-prompt-enable').forEach((button) => {
        button.disabled = value;
        button.classList.toggle('opacity-60', value);
    });
}

function pushErrorMessage(error) {
    const name = String(error?.name || '');
    const message = String(error?.message || '');

    // "Registration failed - push service error" comes from the browser, not
    // from our server: Chrome could not reach Google's FCM push service.
    if (name === 'AbortError' || /push service|Registration failed/i.test(message)) {
        return 'المتصفح ما قدر يتصل بخدمة إشعارات جوجل. تأكد من الإنترنت، وأوقف VPN ومانع الإعلانات، وحدّث خدمات Google Play، وبعدها جرّب مرة ثانية.';
    }

    if (name === 'NotAllowedError' || /permission|denied/i.test(message)) {
        return 'المتصفح رفض إذن الإشعارات. اسمح بها من إعدادات الموقع وجرّب مرة ثانية.';
    }

    if (name === 'InvalidAccessError' || /applicationServerKey/i.test(message)) {
        return 'إعدادات الإشعارات على الخادم غير صحيحة. راسلنا.';
    }

    return message || 'ما قدرنا نفعل الإشعارات. جرّب مرة ثانية.';
}

async function subscribeForPush(registration, key) {
    const options = { userVisibleOnly: true, applicationServerKey: key };

    try {
        return await withTimeout(registration.pushManager.subscribe(options), 20000);
    } catch (error) {
        const retriable = error?.name === 'AbortError'
            || /push service|Registration failed/i.test(String(error?.message || ''));

        if (!retriable) {
            throw error;
        }

        console.warn('First push subscription attempt failed', error);

        // Old Android builds often keep a broken FCM registration that keeps
        // failing until it is cleared, and transient push-service errors tend
        // to succeed on a second try.
        const existing = await registration.pushManager.getSubscription().catch(() => null);
        await existing?.unsubscribe().catch(() => {});
        await new Promise((resolve) => window.setTimeout(resolve, 1500));

        return withTimeout(registration.pushManager.subscribe(options), 20000);
    }
}

async function enable() {
    if (busy) {
        return;
    }

    if (state === 'on') {
        if (Notification.permission !== 'granted') {
            setState(Notification.permission === 'denied' ? 'blocked' : 'off');
            flash('الإشعارات موقوفة من إعدادات المتصفح. اسمح بها وبعدها جرّب مرة ثانية.');

            return;
        }

        await sendTest();

        return;
    }

    if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
        setState('unsupported');
        flash('هذا المتصفح ما بيدعم إشعارات النظام.');

        return;
    }

    setBusy(true);

    try {
        const permission = Notification.permission === 'granted'
            ? 'granted'
            : await Notification.requestPermission();

        if (permission !== 'granted') {
            setState(permission === 'denied' ? 'blocked' : 'off');
            flash('لازم تسمح بالإشعارات من المتصفح عشان توصلك الطلبات.');

            return;
        }

        const key = applicationServerKey();

        if (!key) {
            throw new Error('إعدادات الإشعارات غير مكتملة على الخادم.');
        }

        await navigator.serviceWorker.register('/sw.js');
        const registration = await withTimeout(navigator.serviceWorker.ready, 20000);
        let subscription = await registration.pushManager.getSubscription();

        if (!subscription) {
            subscription = await subscribeForPush(registration, key);
        }

        await api(meta('push-subscribe-url'), subscription.toJSON());

        setState('on');
        closePrompt(false);
        flash('تم تفعيل إشعارات الطلبات ✅');
        await sendTest(true);
    } catch (error) {
        console.warn('Push could not be enabled', error);
        hidePrompt();
        flash(pushErrorMessage(error));
    } finally {
        setBusy(false);
    }
}

async function sendTest(quiet = false) {
    const permission = 'Notification' in window ? Notification.permission : 'default';

    if (permission !== 'granted') {
        setState(permission === 'denied' ? 'blocked' : 'off');

        if (!quiet) {
            flash('لازم تسمح بالإشعارات من المتصفح أول.');
        }

        return;
    }

    setBusy(true);

    try {
        const data = await api(meta('push-test-url'), {});

        if (!quiet) {
            flash(data.ok ? 'أرسلنا إشعار تجريبي — شوفه على جوالك.' : 'الإشعارات مفعّلة، بس التجربة ما وصلت. جرّب مرة ثانية.');
        }
    } catch (error) {
        if (!quiet) {
            flash(error.message || 'ما قدرنا نرسل الإشعار التجريبي.');
        }
    } finally {
        setBusy(false);
    }
}

// Shows a notification from the page's own service worker, with no server or
// push service involved. If this one appears, the device can display our
// notifications and any missing order alert came from the push path instead.
async function diagnose() {
    if (!('Notification' in window) || !('serviceWorker' in navigator)) {
        flash('هذا المتصفح ما بيدعم إشعارات النظام.');

        return;
    }

    if (Notification.permission !== 'granted') {
        setState(Notification.permission === 'denied' ? 'blocked' : 'off');
        flash(Notification.permission === 'denied'
            ? 'الإشعارات محظورة من إعدادات المتصفح. اسمح بها وبعدها جرّب.'
            : 'لازم تسمح بالإشعارات من المتصفح أول.');

        return;
    }

    setBusy(true);

    try {
        let registration = await navigator.serviceWorker.getRegistration();

        if (!registration) {
            await navigator.serviceWorker.register('/sw.js');
            registration = await withTimeout(navigator.serviceWorker.ready, 20000);
        }

        await registration.showNotification('فحص الإشعارات ✅', {
            body: 'إذا شفت هالإشعار، المتصفح قادر يعرض إشعارات الموقع.',
            icon: '/pwa/icon-192.png',
            badge: '/pwa/badge.png',
            dir: 'rtl',
            lang: 'ar',
            tag: 'push-diagnose-'.concat(Date.now()),
            data: { url: '/dashboard' },
        });

        flash('أرسلنا إشعار من المتصفح نفسه. إذا ما ظهر، المشكلة بإعدادات الجهاز مش بالسيرفر.');
    } catch (error) {
        flash('المتصفح رفض يعرض الإشعار: '.concat(error.message || 'خطأ غير معروف'));
    } finally {
        setBusy(false);
    }
}

async function disable() {
    if (busy) {
        return;
    }

    setBusy(true);

    try {
        const registration = await navigator.serviceWorker.getRegistration();
        const subscription = await registration?.pushManager.getSubscription();

        if (subscription) {
            await api(meta('push-unsubscribe-url'), { endpoint: subscription.endpoint }).catch(() => {});
            await subscription.unsubscribe();
        }

        setState('off');
        flash('تم إيقاف إشعارات الطلبات على هذا الجهاز.');
    } catch {
        flash('ما قدرنا نوقف الإشعارات. جرّب مرة ثانية.');
    } finally {
        setBusy(false);
    }
}

async function init() {
    if (!meta('vapid-public-key')) {
        return;
    }

    if (!('Notification' in window) || !('serviceWorker' in navigator) || !('PushManager' in window)) {
        setState('unsupported');

        return;
    }

    const permission = Notification.permission;

    setState(permission === 'denied' ? 'blocked' : 'off');

    try {
        const registration = await navigator.serviceWorker.getRegistration();
        const subscription = registration ? await registration.pushManager.getSubscription() : null;

        if (subscription && permission === 'granted') {
            setState('on');

            // Keep the server in sync if a previous save was interrupted.
            api(meta('push-subscribe-url'), subscription.toJSON()).catch(() => {});
        } else if (subscription && permission === 'denied') {
            // The browser can keep a subscription after the permission is
            // revoked. Remove it so the server stops pushing to a device that
            // cannot display anything.
            const endpoint = subscription.endpoint;

            await subscription.unsubscribe().catch(() => {});
            api(meta('push-unsubscribe-url'), { endpoint }).catch(() => {});
            setState('blocked');
        }
    } catch {
        // Keep the optimistic state; enabling will surface the real error.
    }

    // Register early so notification clicks open the right order even before
    // the owner enables push on this device.
    navigator.serviceWorker.register('/sw.js').catch(() => {});

    // Ask on the first dashboard visit. Browsers only show the system prompt
    // after a tap, so the in-app dialog opens immediately and its button is
    // the gesture that triggers the real permission request.
    if (state === 'off' && Notification.permission === 'default' && !promptWasDismissed()) {
        openPrompt();
    }
}

document.addEventListener('click', (event) => {
    if (event.target.id === 'push-prompt') {
        closePrompt(true);

        return;
    }

    const target = event.target.closest('#push-enable, #push-toggle, #push-disable, #push-diagnose, #push-dismiss, #push-prompt-enable, #push-prompt-later');

    if (!target) {
        return;
    }

    event.preventDefault();

    if (target.id === 'push-enable' || target.id === 'push-toggle' || target.id === 'push-prompt-enable') {
        enable();
    } else if (target.id === 'push-disable') {
        disable();
    } else if (target.id === 'push-diagnose') {
        diagnose();
    } else if (target.id === 'push-prompt-later') {
        closePrompt(true);
    } else {
        window.localStorage.setItem('dukkani.push.dismissed', '1');
        setState(state);
    }
});

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}