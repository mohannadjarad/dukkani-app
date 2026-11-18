/**
 * Render the 6 animated scenes of the "3 signs" reel to JPEG frames.
 *
 *   NODE_PATH=/home/mohannad/.npm/_npx/fd3bca3c548369c0/node_modules \
 *     node social/client-kit/render-reel.cjs
 *
 * Reads the recorded lines from voice/ar/1..6.mp3, writes timeline.json,
 * and captures every frame at 30 fps into /tmp/opencode/client-reel-frames.
 */
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');
const { chromium } = require('playwright');

const DIR = __dirname;
const SRC = path.join(DIR, 'src', 'reel.html');
const VOICE = path.join(DIR, 'voice', 'ar');
const OUT = '/tmp/opencode/client-reel-frames';
const FPS = 30;

const LEAD = 0.35;
const EXTRA = 1.2;
const END_HOLD = 1.5;

const audio = {};
for (let i = 1; i <= 6; i++) {
  const file = path.join(VOICE, `${i}.mp3`);
  if (!fs.existsSync(file)) throw new Error(`missing voice line: ${file}`);
  audio[i] = parseFloat(
    execSync(`ffprobe -v error -show_entries format=duration -of default=nw=1:nk=1 "${file}"`)
      .toString()
      .trim()
  );
}

const duration = {};
for (let i = 1; i <= 6; i++) duration[i] = audio[i] + EXTRA;
duration[6] += END_HOLD;

const timeline = { fps: FPS, lead: LEAD, audio, duration };
fs.writeFileSync(path.join(VOICE, 'timeline.json'), JSON.stringify(timeline, null, 2));
console.log(JSON.stringify(timeline, null, 2));

(async () => {
  fs.rmSync(OUT, { recursive: true, force: true });
  const browser = await chromium.launch();
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
        quality: 95,
      });
      if (f % 150 === 0) {
        console.log(`scene ${scene}: ${f}/${frames} (${((Date.now() - started) / 1000).toFixed(0)}s)`);
      }
    }
    console.log(`scene ${scene} done: ${frames} frames`);
  }

  await browser.close();
  console.log('frames ready');
})();
