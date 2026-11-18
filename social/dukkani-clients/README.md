# Dukkani — "two clients" social kit

Launch pack for one message: **two real stores now run on Dukkani** — one in
Cairo, one in Jenin. No store names, no prices. The CTA is WhatsApp plus the
live demo.

| Asset | Size | Use |
| --- | --- | --- |
| `images/01.png` … `06.png` | 1080 × 1350 | Instagram / Facebook carousel |
| `video/dukkani-clients-reel.mp4` | 1080 × 1920, ~39s | **Main reel** — animated scenes + Arabic voiceover |
| `video/dukkani-clients-reel-music.mp4` | 1080 × 1920, ~39s | **Ready to post** — voiceover + music bed |
| `video/dukkani-clients-reel-silent.mp4` | 1080 × 1920, ~39s | Silent cut — add trending audio in-app |
| `video/dukkani-clients-reel-silent-music.mp4` | 1080 × 1920, ~39s | No voice — music bed only |
| `frames/reel-1.png` … `reel-6.png` | 1080 × 1920 | Reel cover + story frames |
| `captions.md` | — | Arabic (3 dialects), English, LinkedIn + posting steps |

## The story

1. **News** — two real stores, two countries, two currencies.
2. **Problem** — orders lost between WhatsApp and Instagram.
3. **Solution** — an Arabic storefront: menu, cart, checkout, scheduling.
4. **Proof** — the orders board and the reports.
5. **The two stores** — Cairo (EGP) and Jenin (ILS), same system.
6. **CTA** — WhatsApp + live demo.

## Files

```
src/carousel.html       6 slides, switched with ?slide=1…6 (1080×1350)
src/video.html          6 animated scenes, ?scene=1…6 (1080×1920)
render.cjs              renders images/01…06.png
render-video.cjs        renders frames + writes assets/voice/timeline.json
build-video.cjs         builds both MP4s
add-music.cjs           mixes the background track (sidechain ducking)
make-voice.sh           regenerates the voiceover (edge-tts)
assets/                 storefront clips + dashboard crops (no names)
assets/music/           life-of-riley.mp3 (CC BY 4.0)
assets/voice/           1.mp3 … 6.mp3, script-ar.txt, timeline.json
```

## Rebuild

```bash
# 1. carousel (6 × 1080×1350)
NODE_PATH=/path/to/node_modules node social/dukkani-clients/render.cjs

# 2. animation frames (uses assets/voice/timeline.json durations)
NODE_PATH=/path/to/node_modules node social/dukkani-clients/render-video.cjs

# 3. both MP4s
node social/dukkani-clients/build-video.cjs

# 4. music versions (voice + ducked, and music-only)
node social/dukkani-clients/add-music.cjs
```

`render-video.cjs` re-measures every voice file, so **replacing the audio is
enough** — drop new `1.mp3` … `6.mp3` in `assets/voice/` and run steps 2–3.

### Voiceover

`assets/voice/script-ar.txt` is the source script (`n|text`, one line per
scene). `make-voice.sh` regenerates it with `edge-tts`
(`ar-JO-SanaNeural`, rate `-4%`). The mix is loudness-normalised to −16 LUFS.

Timing rules: each scene holds `audio + 1.2s`, scene 6 gets `+1.5s`; scenes are
joined with `0.5s` crossfades; the voice starts `0.35s` into each scene.

### Music

`assets/music/life-of-riley.mp3` — **"Life of Riley" by Kevin MacLeod**
(incompetech.com), licensed under
[Creative Commons Attribution 4.0](https://creativecommons.org/licenses/by/4.0/).

The licence **requires visible attribution** when the track is used. Paste this
credit in the caption or first comment:

> Music: "Life of Riley" — Kevin MacLeod (incompetech.com)
> Licensed under Creative Commons: By Attribution 4.0

In `add-music.cjs` the bed sits at −5 dB under the voice and is sidechain-ducked
so it dips whenever narration plays; both cuts are normalised to −16 LUFS.

Note: Instagram/TikTok may still mute uploaded audio that their content-ID
systems recognise, royalty-free or not. If a post loses its sound, repost with
in-app library music instead. For reach, the **silent** cut plus in-app audio is
still the safest option on business accounts.

## Design system

- Background: warm cream `#fbf3e9` → `#f6ead9`, faint dotted texture.
- Ink `#2b2119`; muted `#7a6a5b`; accent burnt orange `#c2410c`; green `#5f7a5b`.
- Type: Tajawal 400/500/700/800, loaded from `dukkani-app/resources/fonts/`.

## Asset rules

- **No store names.** Storefront clips are cropped below the header/logo;
  dashboard screenshots are cropped to remove the sidebar that carries the name.
  What remains is generic UI, product photos, prices and currencies.
- **No prices** in the creative. Pricing is discussed in the DM only.
- **Cities are named, identities are not** (Cairo / Jenin).
- Claims: Arabic-first, mobile, no order commission, storefront + orders board +
  customer book + reports. Nothing about Pro — it is not built yet.

## CTA

- WhatsApp: <https://wa.me/972569991081>
- Live demo: <https://dukkani-demo.onrender.com>
