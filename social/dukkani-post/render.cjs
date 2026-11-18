/**
 * Render the Dukkani social kit.
 *
 *   NODE_PATH=/path/to/node_modules node social/dukkani-post/render.cjs
 *
 * Outputs:
 *   social/dukkani-post/images/01.png ... 06.png   (1080 x 1350)
 *   social/dukkani-post/frames/reel-1.png ... 6.png (1080 x 1920)
 *
 * The video is assembled from the reel frames with ffmpeg; see README.md.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '../..');
const SRC = path.join(__dirname, 'src');
const IMAGES = path.join(__dirname, 'images');
const FRAMES = path.join(__dirname, 'frames');

fs.mkdirSync(IMAGES, { recursive: true });
fs.mkdirSync(FRAMES, { recursive: true });

const fileUrl = (p) => 'file://' + p;

(async () => {
  const browser = await chromium.launch();

  // ---------- carousel: 1080 x 1350 ----------
  const page = await browser.newPage({
    viewport: { width: 1080, height: 1350 },
    deviceScaleFactor: 1,
  });

  for (let i = 1; i <= 6; i++) {
    await page.goto(`${fileUrl(path.join(SRC, 'carousel.html'))}?slide=${i}`);
    await page.evaluate(() => document.fonts.ready);
    await page.waitForTimeout(350);
    const name = String(i).padStart(2, '0');
    await page.screenshot({ path: path.join(IMAGES, `${name}.png`) });
    console.log(`carousel ${name} ✓`);
  }

  await page.close();

  // ---------- reel frames: 1080 x 1920 ----------
  // Each scene is rendered as a still; ffmpeg adds the motion + transitions.
  const reel = await browser.newPage({
    viewport: { width: 1080, height: 1920 },
    deviceScaleFactor: 1,
  });

  const scenes = ['1', '2', '3', '4', '5', '6'];
  for (const scene of scenes) {
    await reel.goto(`${fileUrl(path.join(SRC, 'reel.html'))}?scene=${scene}`);
    await reel.evaluate(() => document.fonts.ready);
    await reel.waitForTimeout(350);
    await reel.screenshot({ path: path.join(FRAMES, `reel-${scene}.png`) });
    console.log(`reel frame ${scene} ✓`);
  }

  await browser.close();
  console.log('done');
})();
