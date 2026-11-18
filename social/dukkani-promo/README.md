# Dukkani — "studio" promo (33s)

A single, music-driven 33-second vertical promo for launch: it opens on the
problem (orders written on paper and forgotten), shows the storefront, the
in-app notifications, the orders board, today's schedule, then closes on the
brand and a WhatsApp CTA.

Everything on screen is **built** — no Pro, no unbuilt features.

| Asset | Size | Use |
| --- | --- | --- |
| `video/dukkani-promo.mp4` | 1080 × 1920, 33s | **Ready to post** — visuals + music bed |
| `video/dukkani-promo-silent.mp4` | 1080 × 1920, 33s | Silent cut — add trending audio in-app |
| `cover.png` | 1080 × 1920 | Reel cover / thumbnail (frame at t=29.6) |
| `preview/*.jpg` | 540 × 960 | Scene stills for carousels and thumbnails |

## The story (one continuous shot, no cuts)

| # | t | Scene | Copy |
| --- | --- | --- | --- |
| 1 | 0.0 – 5.2 | Order notebook on paper | «كيف تحافظ المتاجر الناجحة على زبائنها لسنين؟» |
| 2 | 4.6 – 9.0 | Isometric city, route to the door | «باهتمام بيوصلهم… حتى باب البيت» |
| 3 | 8.4 – 17.8 | Phone: storefront → order confirmed | «رابط واحد… وكل شي جاهز» |
| 4 | 17.4 – 21.6 | Orders board (one dashboard) | «كل الطلبات… بلوحة وحدة» |
| 5 | 21.2 – 24.8 | Notification centre | «كل طلب جديد… بيوصلك إشعار فورًا» |
| 6 | 24.4 – 27.8 | Today's schedule + 3 mini screens | «إنت مشغول بمطبخك… ودكاني بيستقبل الطلبات عنك» |
| 7 | 27.4 – 30.6 | Heart built from order cards | «تفاصيل صغيرة… بتبني ثقة كبيرة» |
| 8 | 30.2 – 33.0 | Logo + CTA | «متجرك الأونلاين، بالعربي» |

Scenes cross-fade over 0.6 s, so the whole thing reads as one morphing shot.

## Files

```
src/promo.html      all 8 scenes + the deterministic __render(ms) timeline
render.cjs          renders frames (Playwright/Chromium)
build.cjs           encodes frames + adds the music bed
assets/icons.css    Lucide icons as data-URI CSS masks (generated)
assets/icons/       source Lucide SVGs
assets/music/       inspired.mp3 (CC BY 4.0)
```

The UI in the promo is **real HTML**, not screenshots — so it stays crisp at
1080 × 1920 and never shows a real store's name.

## Rebuild

```bash
# 1. frames → /tmp/opencode/promo-frames/00000.jpg … (990 frames @ 30fps)
NODE_PATH=/path/to/node_modules node social/dukkani-promo/render.cjs

# 2. preview stills only
NODE_PATH=/path/to/node_modules node social/dukkani-promo/render.cjs --times=3.2,12,20,27.4,32.5

# 3. encode + music
node social/dukkani-promo/build.cjs
```

`render.cjs` screenshots `window.__render(ms)` for every frame, so the render
is deterministic — the same millisecond always draws the same frame.

## Music

`assets/music/inspired.mp3` — **"Inspired" by Kevin MacLeod** (incompetech.com),
licensed under
[Creative Commons Attribution 4.0](https://creativecommons.org/licenses/by/4.0/).

The licence **requires visible attribution** when the track is used. Paste this
in the caption or first comment:

> Music: "Inspired" — Kevin MacLeod (incompetech.com)
> Licensed under Creative Commons: By Attribution 4.0

The bed is normalised to −16 LUFS with a 1.2 s fade-in and a 3 s fade-out.

Note: Instagram/TikTok may still mute uploaded audio their content-ID systems
recognise, royalty-free or not. For reach, the **silent** cut plus in-app audio
is the safest option on business accounts.

## Voiceover (optional, not included)

There is no narration in this cut — it is designed to work as a silent,
caption-led reel, and the client reel already carries the Arabic voiceover.
If you want one, the same recipe as the clients kit applies: record six lines,
normalise to −16 LUFS, and re-run `build.cjs` with the voice mixed over the
bed (see `../dukkani-clients/add-music.cjs` for the sidechain-ducking pattern).

## Design system

- Background: warm cream `#fbf3e9` → `#f6ead9`, faint dotted texture.
- Ink `#2b2119`; muted `#7a6a5b`; accent burnt orange `#c2410c`; green `#4f6b4b`.
- Type: Tajawal 300/400/500/700/800 from `dukkani-app/resources/fonts/`.
- Icons: Lucide, shipped as CSS masks in `assets/icons.css` (no emoji font).

## Claim guardrails

Only what the app actually does:

- **Notifications** are in-app `StoreNotification`s for `order_created`,
  `order_confirmed` and `order_delivered`. The promo shows those three plus the
  **ready WhatsApp message** (`WhatsAppService` templates → `wa.me` link the
  owner taps). It never claims WhatsApp is automated.
- **Order tracking** — the customer tracking page really exists
  (`/orders/{public_token}`, `OrderTrackingController`).
- **Reports** — revenue, best sellers and repeat customers really exist
  (`AnalyticsService`), so «الأكثر مبيعًا · الإيرادات · الزبائن المتكررين» is
  accurate.
- **No commission** on orders — Dukkani sells a subscription, never a cut.
- **Removed** because they are *not* built: a daily summary notification and a
  "reminder one hour before the order" notification.
- **No prices** in the creative; pricing lives in the DM.
- **No store names**, no real client identities — the storefront is a fictional
  "حلويات بيتية · القاهرة".

## CTA

- WhatsApp: <https://wa.me/972569991081>
- Live demo: <https://dukkani-demo.onrender.com>
