/**
 * Capture the Dukkani demo screenshots used in the portfolio case study.
 *
 * Usage (from dukkani-app/):
 *
 *   NODE_PATH=/path/to/node_modules \
 *   BASE_URL=http://127.0.0.1:8000 \
 *   TRACK_TOKEN=<public order token> \
 *   ORDER_ID=28 CUSTOMER_ID=1 PRODUCT_ID=1 \
 *   node scripts/capture-screenshots.cjs
 *
 * The server must already be running, and the demo database seeded.
 * Output goes to docs/screenshots/ at the repository root by default.
 */

const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const BASE = (process.env.BASE_URL || 'http://127.0.0.1:8000').replace(/\/$/, '');
const STORE = process.env.STORE_SLUG || 'amal-bakes';
const OUT = process.env.OUTPUT_DIR || path.resolve(__dirname, '../../docs/screenshots');
const TRACK_TOKEN = process.env.TRACK_TOKEN || '';
const ORDER_ID = process.env.ORDER_ID || '1';
const CUSTOMER_ID = process.env.CUSTOMER_ID || '1';
const PRODUCT_ID = process.env.PRODUCT_ID || '1';

const DESKTOP = { width: 1440, height: 1000 };
const MOBILE = { width: 390, height: 844 };

fs.mkdirSync(OUT, { recursive: true });

const calmCss = `
  *, *::before, *::after {
    animation-duration: 0s !important;
    animation-delay: 0s !important;
    transition-duration: 0s !important;
    transition-delay: 0s !important;
    caret-color: transparent !important;
  }
  html { scroll-behavior: auto !important; }
`;

async function settle(page, ms = 500) {
  await page.waitForLoadState('domcontentloaded');
  await page.evaluate(() => (document.fonts ? document.fonts.ready : Promise.resolve()));
  await page.waitForTimeout(ms);
}

async function shot(page, name, { fullPage = false } = {}) {
  // JPEG keeps the docs folder small while staying crisp at 2x.
  const file = path.join(OUT, `${name}.jpg`);
  await page.screenshot({ path: file, fullPage, type: 'jpeg', quality: 84 });
  console.log(`  ✓ ${path.relative(process.cwd(), file)}${fullPage ? ' (full page)' : ''}`);
}

async function addProduct(page, slug, { quantity = 1 } = {}) {
  await page.goto(`${BASE}/s/${STORE}/product/${slug}`, { waitUntil: 'domcontentloaded' });
  await settle(page, 300);

  const groups = await page.$$eval('form input[type="radio"]', (inputs) =>
    [...new Set(inputs.map((input) => input.name))],
  );

  for (const name of groups) {
    const input = page.locator(`form input[name="${name}"]`).first();
    if (await input.count()) {
      await input.check({ force: true }).catch(() => {});
    }
  }

  if (quantity > 1) {
    await page.fill('form input[name="quantity"]', String(quantity)).catch(() => {});
  }

  await Promise.all([
    page.waitForLoadState('domcontentloaded'),
    page.locator('form button[type="submit"]').first().click(),
  ]);
  await settle(page, 250);
}

async function main() {
  const browser = await chromium.launch({ headless: true });

  // --- Public storefront ----------------------------------------------------
  const guest = await browser.newContext({
    viewport: DESKTOP,
    deviceScaleFactor: 2,
    locale: 'ar',
    colorScheme: 'light',
  });
  const page = await guest.newPage();
  await page.addInitScript(() => {
    // Keep the layout stable in screenshots.
    window.localStorage.clear();
  });

  console.log('Public storefront');
  await page.goto(`${BASE}/s/${STORE}`, { waitUntil: 'domcontentloaded' });
  await settle(page);
  await page.addStyleTag({ content: calmCss });
  await shot(page, '01-storefront-home');
  await shot(page, '02-storefront-home-full', { fullPage: true });

  await page.goto(`${BASE}/s/${STORE}/menu`, { waitUntil: 'domcontentloaded' });
  await settle(page);
  await page.addStyleTag({ content: calmCss });
  await shot(page, '03-storefront-menu');

  await page.goto(`${BASE}/s/${STORE}/product/honey-almond-cake`, { waitUntil: 'domcontentloaded' });
  await settle(page);
  await page.addStyleTag({ content: calmCss });
  await shot(page, '04-storefront-product');

  await addProduct(page, 'honey-almond-cake', { quantity: 1 });
  await addProduct(page, 'salted-chocolate-cookies', { quantity: 2 });

  await page.goto(`${BASE}/s/${STORE}/cart`, { waitUntil: 'domcontentloaded' });
  await settle(page);
  await page.addStyleTag({ content: calmCss });
  await shot(page, '05-storefront-cart');

  await page.goto(`${BASE}/s/${STORE}/checkout`, { waitUntil: 'domcontentloaded' });
  await settle(page);
  await page.addStyleTag({ content: calmCss });
  await shot(page, '06-storefront-checkout');
  await shot(page, '06b-storefront-checkout-full', { fullPage: true });

  if (TRACK_TOKEN) {
    await page.goto(`${BASE}/orders/${TRACK_TOKEN}`, { waitUntil: 'domcontentloaded' });
    await settle(page);
    await page.addStyleTag({ content: calmCss });
    await shot(page, '07-order-tracking');
    await shot(page, '07b-order-tracking-full', { fullPage: true });
  }

  await page.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
  await settle(page);
  await page.addStyleTag({ content: calmCss });
  await shot(page, '08-login');

  // --- Dashboard ------------------------------------------------------------
  console.log('Seller dashboard');
  const auth = await browser.newContext({
    viewport: DESKTOP,
    deviceScaleFactor: 2,
    locale: 'ar',
    colorScheme: 'light',
  });
  const app = await auth.newPage();
  await app.goto(`${BASE}/login`, { waitUntil: 'domcontentloaded' });
  await settle(app, 250);
  await Promise.all([
    app.waitForURL('**/dashboard', { timeout: 15000 }),
    app.locator('form[action$="/demo-login"] button').first().click(),
  ]);
  await settle(app);

  const dashboardPages = [
    ['09-dashboard-home', '/dashboard', false],
    ['10-orders-board', '/dashboard/orders?view=board', false],
    ['11-orders-list', '/dashboard/orders', false],
    ['12-order-detail', `/dashboard/orders/${ORDER_ID}`, true],
    ['13-products', '/dashboard/products', false],
    ['14-product-edit', `/dashboard/products/${PRODUCT_ID}/edit`, true],
    ['15-customers', '/dashboard/customers', false],
    ['16-customer-profile', `/dashboard/customers/${CUSTOMER_ID}`, false],
    ['17-analytics', '/dashboard/analytics', false],
    ['17b-analytics-full', '/dashboard/analytics', true],
    ['17c-analytics-90-days', '/dashboard/analytics?period=90&sort=sold', false],
    ['18-settings-profile', '/dashboard/settings', false],
    ['18b-settings-delivery', '/dashboard/settings?tab=delivery', false],
    ['18c-settings-hours', '/dashboard/settings?tab=hours', false],
  ];

  for (const [name, url, fullPage] of dashboardPages) {
    await app.goto(`${BASE}${url}`, { waitUntil: 'domcontentloaded' });
    await settle(app);
    await app.addStyleTag({ content: calmCss });
    await shot(app, name, { fullPage });
  }

  // --- Mobile ---------------------------------------------------------------
  console.log('Mobile');
  const state = await auth.storageState();
  const mobileGuest = await browser.newContext({
    viewport: MOBILE,
    deviceScaleFactor: 3,
    isMobile: true,
    hasTouch: true,
    locale: 'ar',
    colorScheme: 'light',
  });
  const mobile = await mobileGuest.newPage();
  await mobile.goto(`${BASE}/s/${STORE}`, { waitUntil: 'domcontentloaded' });
  await settle(mobile);
  await mobile.addStyleTag({ content: calmCss });
  await shot(mobile, '19-storefront-home-mobile');
  await shot(mobile, '19b-storefront-home-mobile-full', { fullPage: true });

  const mobileAuth = await browser.newContext({
    viewport: MOBILE,
    deviceScaleFactor: 3,
    isMobile: true,
    hasTouch: true,
    locale: 'ar',
    colorScheme: 'light',
    storageState: state,
  });
  const mobileApp = await mobileAuth.newPage();
  for (const [name, url] of [
    ['20-dashboard-home-mobile', '/dashboard'],
    ['20b-orders-mobile', '/dashboard/orders'],
    ['20c-analytics-mobile', '/dashboard/analytics'],
  ]) {
    await mobileApp.goto(`${BASE}${url}`, { waitUntil: 'domcontentloaded' });
    await settle(mobileApp);
    await mobileApp.addStyleTag({ content: calmCss });
    await shot(mobileApp, name);
  }

  await browser.close();
  console.log(`\nScreenshots written to ${OUT}`);
}

main().catch((error) => {
  console.error(error);
  process.exit(1);
});
