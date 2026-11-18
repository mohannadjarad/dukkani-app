/**
 * Render the Dukkani "two clients" carousel.
 *
 *   NODE_PATH=/path/to/node_modules node social/dukkani-clients/render.cjs
 *
 * Output: social/dukkani-clients/images/01.png ... 06.png (1080 x 1350)
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const SRC = path.join(__dirname, 'src');
const IMAGES = path.join(__dirname, 'images');

fs.mkdirSync(IMAGES, { recursive: true });

(async () => {
  const exe =
    process.env.CHROMIUM_PATH ||
    ['/usr/bin/chromium', '/usr/bin/chromium-browser', '/usr/bin/google-chrome'].find((p) =>
      fs.existsSync(p)
    );
  const browser = await chromium.launch(exe ? { executablePath: exe } : {});
  const page = await browser.newPage({
    viewport: { width: 1080, height: 1350 },
    deviceScaleFactor: 1,
  });

  for (let i = 1; i <= 6; i++) {
    await page.goto('file://' + path.join(SRC, 'carousel.html') + '?slide=' + i);
    await page.evaluate(() => window.__ready);
    await page.waitForTimeout(300);
    const name = String(i).padStart(2, '0');
    await page.screenshot({ path: path.join(IMAGES, `${name}.png`) });
    console.log(`carousel ${name} ✓`);
  }

  await browser.close();
  console.log('done');
})();
