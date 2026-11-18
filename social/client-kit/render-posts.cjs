/**
 * Render the client-kit feed posts.
 *
 *   NODE_PATH=/home/mohannad/.npm/_npx/fd3bca3c548369c0/node_modules \
 *     node social/client-kit/render-posts.cjs
 *
 * Outputs:
 *   post-education-ig.png  1080x1350  (Instagram 4:5)
 *   post-case-ig.png       1080x1350  (Instagram 4:5)
 *   post-education-fb.png  1200x630   (Facebook 1.91:1)
 *   post-case-fb.png       1200x630   (Facebook 1.91:1)
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const DIR = __dirname;
const OUT = path.join(DIR, 'post');
const PAGES = [
  ['post-education.html', 'post-education-ig.png', 1080, 1350],
  ['post-case.html', 'post-case-ig.png', 1080, 1350],
  ['post-education-fb.html', 'post-education-fb.png', 1200, 630],
  ['post-case-fb.html', 'post-case-fb.png', 1200, 630],
];

(async () => {
  fs.mkdirSync(OUT, { recursive: true });
  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1080, height: 1350 }, deviceScaleFactor: 1 });

  for (const [src, out, width, height] of PAGES) {
    const file = path.join(DIR, 'src', src);
    if (!fs.existsSync(file)) {
      console.log(`skip ${src} (missing)`);
      continue;
    }
    await page.setViewportSize({ width, height });
    await page.goto('file://' + file);
    await page.evaluate(() => window.__ready);
    await page.screenshot({ path: path.join(OUT, out), type: 'png' });
    console.log(`wrote post/${out} (${width}x${height})`);
  }

  await browser.close();
})();
