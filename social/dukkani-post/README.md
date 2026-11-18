# Dukkani — social kit

A ready-to-post pack for promoting custom online stores and SaaS builds, using
Dukkani as the proof.

| Asset | Size | Use |
| --- | --- | --- |
| `images/01.png` … `images/06.png` | 1080 × 1350 | Instagram / Facebook carousel |
| `video/dukkani-voice-reel.mp4` | 1080 × 1920, ~57s | **Main video** — animated scenes + Arabic voiceover |
| `video/dukkani-reel.mp4` | 1080 × 1920, 21s | Silent slideshow fallback (add trending audio in-app) |
| `frames/reel-1.png` … `reel-6.png` | 1080 × 1920 | Reel cover + story frames |
| `captions.md` | — | Arabic + English copy and posting steps |

## The story

1. **Hook** — your customer wants to buy in one tap, not hunt you down in DMs.
2. **Problem** — 20 messages per order, and still no confirmed sale.
3. **Solution** — a storefront that works for you 24/7.
4. **Proof** — Dukkani itself, built from scratch for Amal Bakes in Amman.
5. **SaaS** — the same foundation for your own product idea.
6. **CTA** — WhatsApp `+970 56 999 1081`, message “متجر” or “SaaS”.

## Rebuild the carousel + reel frames

```bash
NODE_PATH=/home/mohannad/.npm/_npx/fd3bca3c548369c0/node_modules \
  node social/dukkani-post/render.cjs
```

The script renders:

- `images/01.png` … `06.png` at 1080 × 1350
- `frames/reel-1.png` … `reel-6.png` at 1080 × 1920

Fonts are loaded from `dukkani-app/resources/fonts/`. Screenshots for slide 4
and reel scene 4 are pre-cropped to 16:10 in `assets/` so nothing is stretched
or cut off inside the frames.

## Rebuild the main video (animated + voiceover)

The main video lives in `src/video.html` — six animated 1080 × 1920 scenes with
kinetic (word-by-word) type, crossfades and an Arabic voiceover.

**1. Render the animation frames** (Playwright, deterministic seek):

```bash
NODE_PATH=/home/mohannad/.npm/_npx/fd3bca3c548369c0/node_modules \
  node social/dukkani-post/render-video.cjs
```

This reads `assets/voice/sana/1..6.mp3`, pauses the CSS animations and
captures every frame at 30 fps into `/tmp/opencode/video-frames/scene-N/`. It
also writes the timing plan to `assets/voice/sana/timeline.json`.

**2. Build the MP4** (encode clips, crossfade, mux the voice):

```bash
node social/dukkani-post/build-video.cjs
```

Output: `video/dukkani-voice-reel.mp4`.

Timing rules (all derived from the audio lengths in `timeline.json`):

- each scene holds `audio + 1.2s`, scene 6 gets an extra `1.5s` hold;
- scenes are joined with `0.5s` crossfades (`xfade`);
- the voice starts `0.35s` into each scene, leaving `0.7s` of silence between
  lines — so the narration lands on a fully-visible scene and never overlaps a
  transition.

### Voiceover

`assets/voice/sana/` holds the six active voice tracks; `script-ar.txt` is the
source script (`n|text`, fully vocalised with harakat so the Arabic reads
naturally). `assets/voice/eleven/` and `assets/voice/female/` are earlier takes
kept for comparison. Both scripts pick `scene-N.mp3` or `N.mp3` inside the
chosen folder, and the mixed narration is loudness-normalised to −16 LUFS; edit
`const VOICE` in `render-video.cjs` and `build-video.cjs` to switch takes.

### Silent fallback (no voiceover)

`video/dukkani-reel.mp4` is the older 21s silent slideshow, built from the
static `frames/reel-1..6.png` with a slow zoom and crossfades; add trending
audio inside Instagram or Facebook when you upload it.

## Design system

- Background: warm cream `#fbf3e9` → `#f6ead9`, with a faint dotted texture.
- Ink: `#2b2119`; muted: `#7a6a5b`.
- Accent: burnt orange `#c2410c`; success green: `#5f7a5b`.
- Type: Tajawal 400/500/700/800, loaded locally.
- Cards: `#fffaf3` on `#e9dcc9` borders, 20–24px radius, soft brown shadows.

## Source files

- `src/carousel.html` — six carousel slides, switched with `?slide=1…6`.
- `src/reel.html` — six static reel scenes (silent fallback), `?scene=1…6`.
- `src/video.html` — six animated scenes for the voice reel, `?scene=1…6`.
- `assets/`, `assets/voice/eleven/` — 16:10 crops and the voiceover.
- `render.cjs` — Playwright renderer for the carousel + static reel frames.
- `render-video.cjs` / `build-video.cjs` — frame capture and MP4 build for the
  animated voice reel.

## Guardrails

- Keep the screenshots and store name as they are; they are real demo data.
- No prices or delivery dates appear in the creative. Quote per project.
- The CTA number is `+970 56 999 1081`; the keywords are “متجر” and “SaaS”.
