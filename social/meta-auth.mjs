#!/usr/bin/env node
// One-time Meta (Facebook Page + Instagram) credential helper.
//
// Doing this by hand is the part everyone gets wrong: the token you copy out of
// the Graph API Explorer is SHORT-LIVED (~1-2 hours), so posts start failing
// the same afternoon. This script exchanges it for a LONG-LIVED (~60 day) user
// token, then reads your Page id, Page token, and Instagram account id straight
// from the Graph API and writes them into social/.env for you.
//
//   1. Create a Meta app (business type): https://developers.facebook.com/apps
//   2. Add products "Facebook Login" and "Instagram Graph API".
//   3. Open the Graph API Explorer, pick your app, grant these permissions:
//        pages_show_list, pages_read_engagement, pages_manage_posts,
//        instagram_basic, instagram_content_publish, business_management
//      then copy the generated "User Access Token" (it is short-lived).
//   4. Put META_APP_ID, META_APP_SECRET and that token in social/.env as
//      META_APP_ID / META_APP_SECRET / META_USER_ACCESS_TOKEN.
//   5. Run:  node social/meta-auth.mjs
//
// Re-run whenever `publish.mjs whoami` starts saying "not configured".

import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const HERE = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(HERE, '..');
const ENV_FILE = path.join(HERE, '.env');
const GRAPH = `https://graph.facebook.com/${process.env.META_GRAPH_VERSION || 'v23.0'}`;

for (const file of [ENV_FILE, path.join(ROOT, '.env')]) {
  if (!existsSync(file)) continue;
  for (const raw of readFileSync(file, 'utf8').split('\n')) {
    const line = raw.trim();
    if (!line || line.startsWith('#')) continue;
    const eq = line.indexOf('=');
    if (eq === -1) continue;
    const k = line.slice(0, eq).trim();
    const v = line.slice(eq + 1).trim().replace(/^['"]|['"]$/g, '');
    if (!(k in process.env)) process.env[k] = v;
  }
}

const APP_ID = process.env.META_APP_ID?.trim();
const APP_SECRET = process.env.META_APP_SECRET?.trim();
const SHORT_TOKEN = (process.env.META_USER_ACCESS_TOKEN || process.env.META_SHORT_TOKEN || '').trim();

const die = (m) => {
  console.error(`\n✖ ${m}\n`);
  process.exit(1);
};

if (!APP_ID || !APP_SECRET) {
  die('Set META_APP_ID and META_APP_SECRET in social/.env first (see .env.example).');
}
if (!SHORT_TOKEN) {
  die(
    'Set META_USER_ACCESS_TOKEN in social/.env to the short-lived token from the\n' +
      '  Graph API Explorer (developers.facebook.com/tools/explorer), then re-run.',
  );
}

async function graph(pathname, params = {}) {
  const url = new URL(`${GRAPH}/${pathname}`);
  for (const [k, v] of Object.entries(params)) url.searchParams.set(k, v);
  const res = await fetch(url);
  const json = await res.json().catch(() => ({}));
  if (!res.ok || json.error) {
    die(`Meta ${pathname} failed (HTTP ${res.status}): ${json.error?.message || res.statusText}`);
  }
  return json;
}

function writeEnv(updates) {
  if (!existsSync(ENV_FILE)) die('social/.env is missing — copy social/.env.example to social/.env first.');
  const lines = readFileSync(ENV_FILE, 'utf8').split('\n');
  const seen = new Set();
  const out = lines.map((line) => {
    const eq = line.indexOf('=');
    const key = eq === -1 ? null : line.slice(0, eq).trim();
    if (key && Object.prototype.hasOwnProperty.call(updates, key)) {
      seen.add(key);
      return `${key}=${updates[key]}`;
    }
    return line;
  });
  const missing = Object.entries(updates).filter(([k]) => !seen.has(k));
  if (missing.length) {
    if (out.length && out[out.length - 1].trim() !== '') out.push('');
    out.push('# Added by social/meta-auth.mjs');
    for (const [k, v] of missing) out.push(`${k}=${v}`);
  }
  writeFileSync(ENV_FILE, out.join('\n'));
}

console.log('\nMeta — exchanging token and reading your Page / Instagram ids...\n');

// 1. Short-lived user token → long-lived user token (~60 days).
const long = await graph('oauth/access_token', {
  grant_type: 'fb_exchange_token',
  client_id: APP_ID,
  client_secret: APP_SECRET,
  fb_exchange_token: SHORT_TOKEN,
});
if (!long.access_token) die('Could not exchange the token. Is META_USER_ACCESS_TOKEN correct and unused?');
console.log('  ✓ Got a long-lived user token');

// 2. Long-lived user token → the Pages you admin (with Page tokens).
const accounts = await graph('me/accounts', {
  access_token: long.access_token,
  fields: 'id,name,access_token',
});
const pages = accounts.data || [];
if (!pages.length) {
  die(
    'This account admins no Facebook Pages. Create a Page (facebook.com/pages/create),\n' +
      '  reconnect it in the Graph API Explorer, and re-run.',
  );
}
console.log(`  ✓ Found ${pages.length} Page(s): ${pages.map((p) => p.name).join(', ')}`);

const wanted = process.env.META_PAGE_NAME?.trim();
const page = (wanted && pages.find((p) => p.name === wanted)) || pages[0];
if (wanted && page.name !== wanted) {
  console.log(`  ! No Page named "${wanted}"; using "${page.name}" instead.`);
}
console.log(`  ✓ Using Page: ${page.name} (${page.id})`);

// 3. Page → linked Instagram professional account (may not exist).
let igId = '';
try {
  const ig = await graph(page.id, {
    access_token: page.access_token,
    fields: 'instagram_business_account{id,username}',
  });
  igId = ig.instagram_business_account?.id || '';
  if (igId) console.log(`  ✓ Instagram: @${ig.instagram_business_account.username} (${igId})`);
  else console.log('  ! No Instagram professional account linked to this Page yet (Facebook-only for now).');
} catch (err) {
  console.log(`  ! Could not read the Instagram account: ${err.message}`);
}

const updates = {
  META_USER_ACCESS_TOKEN: long.access_token,
  FB_PAGE_ID: page.id,
  FB_PAGE_ACCESS_TOKEN: page.access_token,
};
if (igId) updates.IG_USER_ID = igId;
writeEnv(updates);

console.log('\nWrote to social/.env:');
console.log(`  FB_PAGE_ID=${page.id}`);
console.log('  FB_PAGE_ACCESS_TOKEN=<saved>');
console.log(`  META_USER_ACCESS_TOKEN=<saved>`);
if (igId) console.log(`  IG_USER_ID=${igId}`);
console.log('\nNext:  node social/publish.mjs whoami\n');
