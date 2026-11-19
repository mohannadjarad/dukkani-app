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
    flash.timer = window.setTimeout(() => {
        toast.classList.add('hidden');
        toast.classList.remove('flex');
    }, 4200);
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
    const note = $('#push-banner-text');

    if (next === 'unsupported') {
        toggle?.classList.add('hidden');
        banner?.classList.add('hidden');
        banner?.classList.remove('flex');
        disable?.classList.add('hidden');

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

    document.querySelectorAll('#push-enable, #push-toggle, #push-prompt-enable').forEach((button) => {
        button.disabled = value;
        button.classList.toggle('opacity-60', value);
    });
}

async function enable() {
    if (busy) {
        return;
    }

    if (state === 'on') {
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
            subscription = await withTimeout(registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: key,
            }), 20000);
        }

        await api(meta('push-subscribe-url'), subscription.toJSON());

        setState('on');
        closePrompt(false);
        flash('تم تفعيل إشعارات الطلبات ✅');
        await sendTest(true);
    } catch (error) {
        hidePrompt();
        flash(error.message || 'ما قدرنا نفعل الإشعارات. جرّب مرة ثانية.');
    } finally {
        setBusy(false);
    }
}

async function sendTest(quiet = false) {
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

    setState(Notification.permission === 'denied' ? 'blocked' : 'off');

    try {
        const registration = await navigator.serviceWorker.getRegistration();
        const subscription = registration ? await registration.pushManager.getSubscription() : null;

        setState(subscription ? 'on' : Notification.permission === 'denied' ? 'blocked' : 'off');

        if (subscription) {
            // Keep the server in sync if a previous save was interrupted.
            api(meta('push-subscribe-url'), subscription.toJSON()).catch(() => {});
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

    const target = event.target.closest('#push-enable, #push-toggle, #push-disable, #push-dismiss, #push-prompt-enable, #push-prompt-later');

    if (!target) {
        return;
    }

    event.preventDefault();

    if (target.id === 'push-enable' || target.id === 'push-toggle' || target.id === 'push-prompt-enable') {
        enable();
    } else if (target.id === 'push-disable') {
        disable();
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