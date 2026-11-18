/**
 * Render the animated Dukkani reel, frame by frame.
 *
 *   NODE_PATH=/path/to/node_modules node social/dukkani-post/render-video.cjs
 *
 * Requires the voice tracks in assets/voice/eleven/scene-1..6.mp3 (see the
 * `VOICE` constant to switch takes). Writes JPEG frames to
 * /tmp/opencode/video-frames/scene-N/ and a timeline JSON used by
 * build-video.cjs.
 */
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const { chromium } = require('playwright');

const DIR = __dirname;
const SRC = path.join(DIR, 'src', 'video.html');
const VOICE = path.join(DIR, 'assets', 'voice', 'sana');
const OUT = '/tmp/opencode/video-frames';
const FPS = 30;

const LEAD = 0.35;   // silence before the first line
const GAP = 0.7;     // silence between scenes (matches the 0.5s crossfade)
const EXTRA = 1.2;   // tail after each line
const END_HOLD = 1.5;

const voiceFile = (i) => {
  const a = path.join(VOICE, `scene-${i}.mp3`);
  return fs.existsSync(a) ? a : path.join(VOICE, `${i}.mp3`);
};

const audio = {};
for (let i = 1; i <= 6; i++) {
  const file = voiceFile(i);
  audio[i] = parseFloat(
    execSync(`ffprobe -v error -show_entries format=duration -of default=nw=1:nk=1 "${file}"`)
      .toString()
      .trim()
  );
}

const duration = {};
for (let i = 1; i <= 6; i++) duration[i] = audio[i] + EXTRA;
duration[6] += END_HOLD;

const total = Object.values(duration).reduce((a, b) => a + b, 0) - 5 * 0.5;
const tail = duration[6] - audio[6] - 0.35;

const timeline = { fps: FPS, lead: LEAD, gap: GAP, tail, audio, duration, total };
fs.writeFileSync(path.join(VOICE, 'timeline.json'), JSON.stringify(timeline, null, 2));
console.log('timeline', JSON.stringify(timeline, null, 2));

(async () => {
  fs.rmSync(OUT, { recursive: true, force: true });
  // The bundled Playwright build may be missing on this machine; prefer a
  // system Chromium when present.
  const exe =
    process.env.CHROMIUM_PATH ||
    ['/usr/bin/chromium', '/usr/bin/chromium-browser', '/usr/bin/google-chrome'].find((p) =>
      fs.existsSync(p)
    );
  const browser = await chromium.launch(exe ? { executablePath: exe } : {});
  const page = await browser.newPage({
    viewport: { width: 1080, height: 1920 },
    deviceScaleFactor: 1,
  });

  for (let scene = 1; scene <= 6; scene++) {
    const dir = path.join(OUT, `scene-${scene}`);
    fs.mkdirSync(dir, { recursive: true });

    await page.goto('file://' + SRC + '?scene=' + scene);
    await page.evaluate(() => window.__ready);
    await page.evaluate(() => window.__pause());

    const frames = Math.round(duration[scene] * FPS);
    const started = Date.now();

    for (let f = 0; f < frames; f++) {
      await page.evaluate((ms) => window.__seek(ms), (f * 1000) / FPS);
      await page.screenshot({
        path: path.join(dir, String(f).padStart(5, '0') + '.jpg'),
        type: 'jpeg',
        quality: 96,
      });
      if (f % 150 === 0) {
        const secs = ((Date.now() - started) / 1000).toFixed(0);
        console.log(`scene ${scene}: frame ${f}/${frames} (${secs}s)`);
      }
    }

    console.log(`scene ${scene} done: ${frames} frames`);
  }

  await browser.close();
  console.log('all frames rendered');
})();
