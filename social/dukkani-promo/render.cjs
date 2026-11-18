/**
 * Render the Dukkani "studio" promo.
 *
 *   node social/dukkani-promo/render.cjs                  # all 990 frames
 *   node social/dukkani-promo/render.cjs --times=1,6,12   # preview stills
 *
 * Frames go to /tmp/opencode/promo-frames/%05d.jpg, previews to
 * /tmp/opencode/promo-preview/.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const DIR = __dirname;
const SRC = path.join(DIR, 'src', 'promo.html');
const OUT = '/tmp/opencode/promo-frames';
const PREVIEW = '/tmp/opencode/promo-preview';
const FPS = 30;
const TOTAL = 33;

const arg = process.argv.find((a) => a.startsWith('--times='));
const times = arg ? arg.split('=')[1].split(',').map(Number) : null;

(async () => {
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
  page.on('pageerror', (e) => console.error('PAGE ERROR:', e.message));
  page.on('console', (m) => { if (m.type() === 'error') console.error('CONSOLE:', m.text()); });

  await page.goto('file://' + SRC);
  await page.evaluate(() => window.__ready);

  if (times) {
    fs.rmSync(PREVIEW, { recursive: true, force: true });
    fs.mkdirSync(PREVIEW, { recursive: true });
    for (const s of times) {
      await page.evaluate((ms) => window.__render(ms), s * 1000);
      const file = path.join(PREVIEW, `t-${String(s).replace('.', '_')}.png`);
      await page.screenshot({ path: file, type: 'png' });
      console.log('preview', file);
    }
  } else {
    fs.rmSync(OUT, { recursive: true, force: true });
    fs.mkdirSync(OUT, { recursive: true });
    const frames = Math.round(TOTAL * FPS);
    const started = Date.now();
    for (let f = 0; f < frames; f++) {
      await page.evaluate((ms) => window.__render(ms), (f * 1000) / FPS);
      await page.screenshot({
        path: path.join(OUT, String(f).padStart(5, '0') + '.jpg'),
        type: 'jpeg',
        quality: 95,
      });
      if (f % 120 === 0) {
        console.log(`frame ${f}/${frames} (${((Date.now() - started) / 1000).toFixed(0)}s)`);
      }
    }
    console.log(`done: ${frames} frames in ${((Date.now() - started) / 1000).toFixed(0)}s`);
  }

  await browser.close();
})();
