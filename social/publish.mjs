#!/usr/bin/env node
// Publish the Dukkani social kit to Facebook, Instagram, and LinkedIn.
//
// Zero dependencies: global fetch / FormData / Blob (Node 18+), plus a tiny
// .env reader. Credentials live in social/.env (gitignored) — see .env.example.
//
//   node social/publish.mjs whoami
//   node social/publish.mjs post --platform all --content carousel --lang ar
//   node social/publish.mjs post --platform linkedin --content carousel --lang en
//   node social/publish.mjs post --platform facebook --content carousel --dry-run
//   node social/publish.mjs stage                 # copy carousel into the landing site
//   node social/publish.mjs stage --content all
//
// Facebook and LinkedIn upload the files straight from disk. Instagram needs
// public URLs (see PUBLIC_MEDIA_BASE_URL in social/.env.example).

import { readFileSync, existsSync, mkdirSync, copyFileSync } from 'node:fs';
import { readFile, stat } from 'node:fs/promises';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const HERE = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(HERE, '..');
const KIT = path.join(HERE, 'dukkani-post');
const IMAGES_DIR = path.join(KIT, 'images');
const VIDEO_DIR = path.join(KIT, 'video');
const LANDING_SOCIAL = path.join(ROOT, 'dukkani-landing', 'social');

// ---------------------------------------------------------------------------
// .env loading + tiny CLI helpers
// ---------------------------------------------------------------------------

function loadEnv() {
  for (const file of [path.join(HERE, '.env'), path.join(ROOT, '.env')]) {
    if (!existsSync(file)) continue;
    for (const raw of readFileSync(file, 'utf8').split('\n')) {
      const line = raw.trim();
      if (!line || line.startsWith('#')) continue;
      const eq = line.indexOf('=');
      if (eq === -1) continue;
      const key = line.slice(0, eq).trim();
      let val = line.slice(eq + 1).trim();
      if (
        (val.startsWith('"') && val.endsWith('"')) ||
        (val.startsWith("'") && val.endsWith("'"))
      ) {
        val = val.slice(1, -1);
      }
      if (!(key in process.env)) process.env[key] = val;
    }
  }
}

const env = (name) => (process.env[name] ?? '').trim();

let DRY_RUN = false;
function need(name) {
  const v = env(name);
  if (!v) {
    if (DRY_RUN) return 'DRY_RUN_PLACEHOLDER';
    die(`Missing ${name}. Add it to social/.env (see social/.env.example).`);
  }
  return v;
}

const log = (...a) => console.log(...a);
const dry = (msg) => log(`  [dry-run] ${msg}`);
function die(msg) {
  console.error(`\n✖ ${msg}\n`);
  process.exit(1);
}

function parseArgs(argv) {
  const out = { _: [] };
  for (let i = 0; i < argv.length; i++) {
    const a = argv[i];
    if (a.startsWith('--')) {
      const key = a.slice(2);
      const next = argv[i + 1];
      if (next === undefined || next.startsWith('--')) out[key] = true;
      else out[key] = argv[++i];
    } else out._.push(a);
  }
  return out;
}

const mimeOf = (file) =>
  file.endsWith('.png') ? 'image/png'
  : file.endsWith('.jpg') || file.endsWith('.jpeg') ? 'image/jpeg'
  : file.endsWith('.mp4') ? 'video/mp4'
  : 'application/octet-stream';

// ---------------------------------------------------------------------------
// Captions: parsed from captions.md so the copy stays single-sourced
// ---------------------------------------------------------------------------

function readCaptions() {
  const md = readFileSync(path.join(KIT, 'captions.md'), 'utf8');
  const lines = md.split('\n');
  const blocks = {}; // "carousel|ar" -> [lines]
  let section = null; // 'carousel' | 'video'
  let lang = null; // 'ar' | 'en'
  let buf = null;

  const flush = () => {
    if (section && lang && buf) blocks[`${section}|${lang}`] = buf.join('\n').trim();
    buf = null;
  };

  for (const line of lines) {
    if (line.startsWith('## ')) {
      flush();
      const t = line.toLowerCase();
      section = t.includes('carousel') ? 'carousel' : t.includes('video') ? 'video' : null;
      lang = null;
    } else if (line.startsWith('### ')) {
      flush();
      const t = line.toLowerCase();
      lang = t.includes('arabic') ? 'ar' : t.includes('english') ? 'en' : null;
    } else if (buf !== null && line.startsWith('>')) {
      buf.push(line.replace(/^>\s?/, ''));
    } else if (buf !== null && line.trim() === '') {
      // blank line inside/after a blockquote: keep it, but stop at a new heading
      buf.push('');
    } else if (buf === null && lang && line.startsWith('>')) {
      buf = [line.replace(/^>\s?/, '')];
    }
  }
  flush();

  const clean = (k) => (blocks[k] || '').replace(/\n{3,}/g, '\n\n').trim();
  return {
    carousel: { ar: clean('carousel|ar'), en: clean('carousel|en') },
    video: { ar: clean('video|ar'), en: clean('video|en') },
  };
}

// ---------------------------------------------------------------------------
// Asset sets
// ---------------------------------------------------------------------------

function carouselImages() {
  const names = ['01.png', '02.png', '03.png', '04.png', '05.png', '06.png'];
  const files = names.map((n) => path.join(IMAGES_DIR, n));
  for (const f of files) if (!existsSync(f)) die(`Missing image: ${f}`);
  return files;
}

function reelVideo() {
  const file = path.join(VIDEO_DIR, 'dukkani-voice-reel.mp4');
  if (!existsSync(file)) die(`Missing video: ${file}`);
  return file;
}

/** Public URLs for the Instagram carousel images (and optionally the reel). */
function publicUrls(files) {
  const override = env('IG_IMAGE_URLS');
  const base = (env('PUBLIC_MEDIA_BASE_URL') || (DRY_RUN ? 'https://example.com/social' : '')).replace(/\/+$/, '');
  return files.map((f, i) => {
    if (override) return override.split(',').map((s) => s.trim())[i] || '';
    if (!base) return '';
    return `${base}/${path.basename(f)}`;
  });
}

/** Public URL for the story video (9:16 reel). */
function storyVideoUrl() {
  const base = (env('PUBLIC_MEDIA_BASE_URL') || (DRY_RUN ? 'https://example.com/social' : '')).replace(/\/+$/, '');
  return base ? `${base}/dukkani-voice-reel.mp4` : '';
}

// ---------------------------------------------------------------------------
// Meta Graph API (Facebook Page + Instagram)
// ---------------------------------------------------------------------------

const graphBase = () => `https://graph.facebook.com/${env('META_GRAPH_VERSION') || 'v23.0'}`;

async function graphCall(pathname, { method = 'GET', params, token, body } = {}) {
  const url = new URL(`${graphBase()}/${pathname}`);
  if (params) for (const [k, v] of Object.entries(params)) url.searchParams.set(k, v);
  const headers = {};
  let payload = body;
  if (token) headers.Authorization = `Bearer ${token}`;
  if (body && !(body instanceof FormData)) {
    headers['Content-Type'] = 'application/json';
    payload = JSON.stringify(body);
  }
  const res = await fetch(url, { method, headers, body: payload });
  const text = await res.text();
  let json;
  try {
    json = text ? JSON.parse(text) : {};
  } catch {
    json = { raw: text };
  }
  if (!res.ok || json.error) {
    const e = json.error || {};
    die(`Meta ${method} ${pathname} failed (HTTP ${res.status}): ${e.message || text}`);
  }
  return json;
}

async function postFacebookCarousel(caption) {
  const token = need('FB_PAGE_ACCESS_TOKEN');
  const page = need('FB_PAGE_ID');
  const files = carouselImages();
  log('Facebook — uploading 6 photos (unpublished), then creating the post');
  const ids = [];
  for (const f of files) {
    if (DRY_RUN) {
      dry(`upload ${path.basename(f)} → /${page}/photos (published=false)`);
      ids.push(`DRY_${path.basename(f)}`);
      continue;
    }
    const form = new FormData();
    form.append('published', 'false');
    form.append('source', new Blob([await readFile(f)], { type: mimeOf(f) }), path.basename(f));
    form.append('access_token', token);
    const res = await graphCall(`${page}/photos`, { method: 'POST', body: form });
    ids.push(res.id);
    log(`  ✓ ${path.basename(f)}`);
  }
  if (DRY_RUN) return dry(`POST /${page}/feed with message + ${ids.length} attached_media`);

  const form = new FormData();
  form.append('message', caption);
  ids.forEach((id, i) => form.append(`attached_media[${i}]`, JSON.stringify({ media_fbid: id })));
  form.append('access_token', token);
  const res = await graphCall(`${page}/feed`, { method: 'POST', body: form });
  log(`  ✓ Facebook post: https://facebook.com/${res.id}`);
}

async function postFacebookVideo(caption) {
  const token = need('FB_PAGE_ACCESS_TOKEN');
  const page = need('FB_PAGE_ID');
  const file = reelVideo();
  if (DRY_RUN) return dry(`upload ${path.basename(file)} → /${page}/videos`);
  const form = new FormData();
  form.append('description', caption);
  form.append('source', new Blob([await readFile(file)], { type: 'video/mp4' }), path.basename(file));
  form.append('access_token', token);
  log('Facebook — uploading the reel…');
  const res = await graphCall(`${page}/videos`, { method: 'POST', body: form });
  log(`  ✓ Facebook video: https://facebook.com/${res.id}`);
}

async function postInstagramCarousel(caption) {
  const token = need('FB_PAGE_ACCESS_TOKEN');
  const ig = need('IG_USER_ID');
  const files = carouselImages();
  const urls = publicUrls(files);
  if (urls.some((u) => !u)) {
    die(
      'Instagram needs public image URLs. Set PUBLIC_MEDIA_BASE_URL in social/.env\n' +
        '  (run `node social/publish.mjs stage` and point it at your deployed landing/social/),\n' +
        '  or set IG_IMAGE_URLS to a comma-separated list of 6 public URLs.',
    );
  }

  log('Instagram — creating 6 carousel item containers');
  const children = [];
  for (let i = 0; i < urls.length; i++) {
    if (DRY_RUN) {
      dry(`container ${i + 1}: image_url=${urls[i]}`);
      children.push(`DRY_${i}`);
      continue;
    }
    const res = await graphCall(`${ig}/media`, {
      method: 'POST',
      params: { image_url: urls[i], is_carousel_item: 'true', access_token: token },
    });
    children.push(res.id);
    log(`  ✓ item ${i + 1}`);
  }
  if (DRY_RUN) {
    dry(`POST /${ig}/media (CAROUSEL, children=${children.join(',')})`);
    dry(`POST /${ig}/media_publish (creation_id=<container>)`);
    return;
  }
  const container = await graphCall(`${ig}/media`, {
    method: 'POST',
    params: {
      media_type: 'CAROUSEL',
      children: children.join(','),
      caption,
      access_token: token,
    },
  });
  const pub = await graphCall(`${ig}/media_publish`, {
    method: 'POST',
    params: { creation_id: container.id, access_token: token },
  });
  log(`  ✓ Instagram post: ${pub.id}`);
}

async function postInstagramReel(caption) {
  const token = need('FB_PAGE_ACCESS_TOKEN');
  const ig = need('IG_USER_ID');
  const [url] = publicUrls([reelVideo()]);
  if (!url) {
    die('Instagram Reels need a public video URL. Set PUBLIC_MEDIA_BASE_URL (run `publish.mjs stage --content video`).');
  }
  if (DRY_RUN) {
    dry(`POST /${ig}/media (REELS, video_url=${url})`);
    dry(`POST /${ig}/media_publish`);
    return;
  }
  log('Instagram — creating the reel container…');
  const container = await graphCall(`${ig}/media`, {
    method: 'POST',
    params: { media_type: 'REELS', video_url: url, caption, access_token: token },
  });
  log(`  · container ${container.id}, waiting for processing...`);
  await waitForIgContainer(container.id, token);
  const pub = await graphCall(`${ig}/media_publish`, {
    method: 'POST',
    params: { creation_id: container.id, access_token: token },
  });
  log(`  ✓ Instagram reel: ${pub.id}`);
}

/** Poll an Instagram media container until it is ready to publish. */
async function waitForIgContainer(id, token) {
  for (let attempt = 0; attempt < 60; attempt++) {
    const st = await graphCall(id, { params: { fields: 'status_code', access_token: token } });
    if (st.status_code === 'FINISHED') return;
    if (st.status_code === 'ERROR' || st.status_code === 'EXPIRED') {
      die(`Instagram container ${id} ended in status ${st.status_code}.`);
    }
    await new Promise((r) => setTimeout(r, 3000));
  }
  die(`Instagram container ${id} did not finish processing in time.`);
}

async function postInstagramStory() {
  const token = need('FB_PAGE_ACCESS_TOKEN');
  const ig = need('IG_USER_ID');
  const url = storyVideoUrl();
  if (!url) {
    die(
      'Instagram stories need a public video URL. Set PUBLIC_MEDIA_BASE_URL in social/.env\n' +
        '  (run `node social/publish.mjs stage --content video` and deploy the landing site).',
    );
  }
  if (DRY_RUN) {
    dry(`POST /${ig}/media (STORIES, video_url=${url})`);
    dry('poll /<container>?fields=status_code until FINISHED');
    dry(`POST /${ig}/media_publish (creation_id=<container>)`);
    return;
  }
  log('Instagram — creating the story container...');
  const container = await graphCall(`${ig}/media`, {
    method: 'POST',
    params: { media_type: 'STORIES', video_url: url, access_token: token },
  });
  log(`  · container ${container.id}, waiting for processing...`);
  await waitForIgContainer(container.id, token);
  const pub = await graphCall(`${ig}/media_publish`, {
    method: 'POST',
    params: { creation_id: container.id, access_token: token },
  });
  log(`  ✓ Instagram story: ${pub.id}`);
}

async function postFacebookVideoStory() {
  const token = need('FB_PAGE_ACCESS_TOKEN');
  const page = need('FB_PAGE_ID');
  const url = storyVideoUrl();
  if (!url) {
    die(
      'Facebook stories need a public video URL. Set PUBLIC_MEDIA_BASE_URL in social/.env\n' +
        '  (run `node social/publish.mjs stage --content video` and deploy the landing site).',
    );
  }
  if (DRY_RUN) {
    dry(`POST /${page}/video_stories {upload_phase:start}`);
    dry(`POST upload_url with file_url=${url}`);
    dry(`POST /${page}/video_stories {upload_phase:finish}`);
    return;
  }
  log('Facebook — starting the story upload session...');
  const start = await graphCall(`${page}/video_stories`, {
    method: 'POST',
    params: { upload_phase: 'start', access_token: token },
  });
  log('  · uploading the video...');
  const upload = await fetch(start.upload_url, {
    method: 'POST',
    headers: { Authorization: `OAuth ${token}`, file_url: url },
  });
  const upJson = await upload.json().catch(() => ({}));
  if (!upload.ok || upJson.success === false) {
    die(`Facebook story upload failed (HTTP ${upload.status}): ${JSON.stringify(upJson)}`);
  }
  const fin = await graphCall(`${page}/video_stories`, {
    method: 'POST',
    params: { upload_phase: 'finish', video_id: start.video_id, access_token: token },
  });
  log(`  ✓ Facebook story: ${fin.post_id}`);
}

// ---------------------------------------------------------------------------
// LinkedIn (personal profile)
// ---------------------------------------------------------------------------

const liBase = 'https://api.linkedin.com';
function liHeaders(extra = {}) {
  return {
    Authorization: `Bearer ${need('LINKEDIN_ACCESS_TOKEN')}`,
    'Linkedin-Version': env('LINKEDIN_VERSION') || '202610',
    'X-Restli-Protocol-Version': '2.0.0',
    ...extra,
  };
}

async function liCall(pathname, { method = 'GET', body, headers = {} } = {}) {
  const res = await fetch(`${liBase}${pathname}`, {
    method,
    headers: liHeaders(body ? { 'Content-Type': 'application/json', ...headers } : headers),
    body: body ? JSON.stringify(body) : undefined,
  });
  const text = await res.text();
  let json = {};
  try {
    json = text ? JSON.parse(text) : {};
  } catch {
    json = { raw: text };
  }
  if (!res.ok) die(`LinkedIn ${method} ${pathname} failed (HTTP ${res.status}): ${json.message || text}`);
  return { json, res };
}

async function linkedinAuthor() {
  const fromEnv = env('LINKEDIN_AUTHOR_URN');
  if (fromEnv) return fromEnv;
  if (DRY_RUN) return 'urn:li:person:DRY_RUN';
  try {
    const { json } = await liCall('/v2/userinfo');
    if (json.sub) return `urn:li:person:${json.sub}`;
  } catch {}
  try {
    const { json } = await liCall('/v2/me');
    if (json.id) return `urn:li:person:${json.id}`;
  } catch {}
  die('Could not detect your LinkedIn member URN. Set LINKEDIN_AUTHOR_URN in social/.env.');
}

async function liUploadImage(file) {
  const owner = await linkedinAuthor();
  const { json } = await liCall('/rest/images?action=initializeUpload', {
    method: 'POST',
    body: { initializeUploadRequest: { owner } },
  });
  const { uploadUrl, image } = json.value;
  const bytes = await readFile(file);
  const put = await fetch(uploadUrl, {
    method: 'PUT',
    headers: { Authorization: `Bearer ${need('LINKEDIN_ACCESS_TOKEN')}`, 'Content-Type': mimeOf(file) },
    body: bytes,
  });
  if (!put.ok) die(`LinkedIn image upload failed (HTTP ${put.status}) for ${path.basename(file)}`);
  return image; // urn:li:image:...
}

/** Upload one MP4 through the LinkedIn Videos API and return its urn:li:video: URN. */
async function liUploadVideo(file) {
  const owner = await linkedinAuthor();
  const { size } = await stat(file);

  if (DRY_RUN) {
    dry(`POST /rest/videos?action=initializeUpload (owner=${owner}, fileSizeBytes=${size})`);
    dry('PUT each ~4 MB part to its uploadUrl + collect ETag per part');
    dry('POST /rest/videos?action=finalizeUpload');
    dry('poll GET /rest/videos/<urn> until status=AVAILABLE');
    return 'urn:li:video:DRY_RUN';
  }

  log(`LinkedIn — initializing video upload (${(size / 1048576).toFixed(1)} MB)...`);
  const { json } = await liCall('/rest/videos?action=initializeUpload', {
    method: 'POST',
    body: {
      initializeUploadRequest: {
        owner,
        fileSizeBytes: size,
        uploadCaptions: false,
        uploadThumbnail: false,
      },
    },
  });
  const value = json.value || {};
  const { video, uploadToken = '', uploadInstructions = [] } = value;
  if (!video || !uploadInstructions.length) {
    die(`LinkedIn video initializeUpload returned no upload instructions: ${JSON.stringify(json)}`);
  }

  const bytes = await readFile(file);
  const partIds = [];
  for (let i = 0; i < uploadInstructions.length; i++) {
    const { firstByte, lastByte, uploadUrl } = uploadInstructions[i];
    const chunk = bytes.subarray(firstByte, lastByte + 1);
    log(`  · part ${i + 1}/${uploadInstructions.length} (${chunk.length} bytes)`);
    // The signed upload URL carries its own auth; do not send the OAuth header.
    const put = await fetch(uploadUrl, {
      method: 'PUT',
      headers: { 'Content-Type': 'application/octet-stream' },
      body: chunk,
    });
    if (!put.ok) die(`LinkedIn video part ${i + 1} upload failed (HTTP ${put.status}).`);
    const etag = put.headers.get('etag');
    if (!etag) die(`LinkedIn video part ${i + 1} returned no ETag header.`);
    partIds.push(etag.replace(/^"|"$/g, ''));
  }

  log('  · finalizing upload...');
  await liCall('/rest/videos?action=finalizeUpload', {
    method: 'POST',
    body: { finalizeUploadRequest: { video, uploadToken, uploadedPartIds: partIds } },
  });

  log('  · waiting for the video to finish processing...');
  await waitForLiVideo(video);
  return video;
}

/** Poll a LinkedIn video until it is AVAILABLE and ready to post. */
async function waitForLiVideo(urn) {
  const pathname = `/rest/videos/${encodeURIComponent(urn)}`;
  for (let attempt = 0; attempt < 120; attempt++) {
    const { json } = await liCall(pathname);
    const status = json.status || json.value?.status;
    if (status === 'AVAILABLE') return;
    if (status === 'PROCESSING_FAILED') {
      const why = json.processingFailureReason || json.value?.processingFailureReason || 'unknown reason';
      die(`LinkedIn video processing failed: ${why}`);
    }
    await new Promise((r) => setTimeout(r, 3000));
  }
  die('LinkedIn video did not finish processing in time.');
}

async function postLinkedIn({ caption, images, video }) {
  const author = await linkedinAuthor();
  log(`LinkedIn — author ${author}`);

  let content;
  if (video) {
    const urn = await liUploadVideo(video);
    const title = env('LINKEDIN_VIDEO_TITLE') || 'Dukkani — Arabic online storefront with an orders dashboard';
    content = { media: { id: urn, title } };
    if (!DRY_RUN) log('  ✓ uploaded video');
  } else {
    const urns = [];
    for (const f of images) {
      if (DRY_RUN) {
        dry(`initializeUpload + PUT ${path.basename(f)}`);
        urns.push(`DRY_${path.basename(f)}`);
        continue;
      }
      urns.push(await liUploadImage(f));
      log(`  ✓ uploaded ${path.basename(f)}`);
    }
    content =
      urns.length > 1
        ? { multiImage: { images: urns.map((id) => ({ id })) } }
        : { media: { id: urns[0] } };
  }

  const payload = {
    author,
    commentary: caption,
    visibility: 'PUBLIC',
    distribution: { feedDistribution: 'MAIN_FEED', targetEntities: [], thirdPartyDistributionChannels: [] },
    lifecycleState: 'PUBLISHED',
    isReshareDisabledByAuthor: false,
    content,
  };

  if (DRY_RUN) return dry(`POST /rest/posts ${video ? 'with the video' : 'with image(s)'}`);

  const { res } = await liCall('/rest/posts', { method: 'POST', body: payload });
  const id = res.headers.get('x-restli-id');
  log(`  ✓ LinkedIn post: ${id || '(created)'}`);
}

// ---------------------------------------------------------------------------
// whoami
// ---------------------------------------------------------------------------

async function whoami() {
  log('\nFacebook / Instagram');
  if (env('FB_PAGE_ID') && env('FB_PAGE_ACCESS_TOKEN')) {
    try {
      const page = await graphCall(`${env('FB_PAGE_ID')}?fields=id,name`, {
        token: env('FB_PAGE_ACCESS_TOKEN'),
      });
      log(`  ✓ Page: ${page.name} (${page.id})`);
    } catch {}
  } else {
    log('  · not configured');
  }
  if (env('IG_USER_ID') && env('FB_PAGE_ACCESS_TOKEN')) {
    try {
      const ig = await graphCall(`${env('IG_USER_ID')}?fields=username,followers_count`, {
        token: env('FB_PAGE_ACCESS_TOKEN'),
      });
      log(`  ✓ Instagram: @${ig.username} (${ig.followers_count ?? '?'} followers)`);
    } catch {}
  } else {
    log('  · not configured');
  }

  log('\nLinkedIn');
  if (env('LINKEDIN_ACCESS_TOKEN')) {
    try {
      const urn = await linkedinAuthor();
      log(`  ✓ Author: ${urn}`);
    } catch {}
  } else {
    log('  · not configured');
  }
  log('');
}

// ---------------------------------------------------------------------------
// stage: copy assets into the landing site so they get a public URL
// ---------------------------------------------------------------------------

function stage(content) {
  mkdirSync(LANDING_SOCIAL, { recursive: true });
  const copied = [];
  if (content === 'carousel' || content === 'all') {
    for (const n of ['01.png', '02.png', '03.png', '04.png', '05.png', '06.png']) {
      const src = path.join(IMAGES_DIR, n);
      copyFileSync(src, path.join(LANDING_SOCIAL, n));
      copied.push(n);
    }
  }
  if (content === 'video' || content === 'all') {
    const n = 'dukkani-voice-reel.mp4';
    copyFileSync(path.join(VIDEO_DIR, n), path.join(LANDING_SOCIAL, n));
    copied.push(n);
  }
  log(`Staged into dukkani-landing/social/: ${copied.join(', ')}`);
  log('Deploy dukkani-landing, then set PUBLIC_MEDIA_BASE_URL=<site>/social in social/.env');
}

// ---------------------------------------------------------------------------
// main
// ---------------------------------------------------------------------------

loadEnv();
const args = parseArgs(process.argv.slice(2));
DRY_RUN = Boolean(args['dry-run']);
const command = args._[0] || 'help';
const content = args.content || 'carousel';
const lang = args.lang || 'ar';

const platforms = (args.platform === 'all' || !args.platform)
  ? ['facebook', 'instagram', 'linkedin']
  : String(args.platform).split(',').map((s) => s.trim().toLowerCase());

const captions = readCaptions();
const captionFor = (kind) => {
  const c = captions[kind]?.[lang];
  if (!c) die(`No ${lang} caption for "${kind}" in captions.md`);
  return c;
};

try {
  if (command === 'whoami') {
    await whoami();
  } else if (command === 'captions') {
    log(`\n----- ${content} / ${lang} -----\n${captionFor(content)}\n`);
  } else if (command === 'stage') {
    stage(content === 'all' ? 'all' : content);
  } else if (command === 'post') {
    if (!['carousel', 'video', 'story'].includes(content)) {
      die(`Unknown content "${content}". Use carousel, video, or story.`);
    }
    const caption = content === 'story' ? '' : captionFor(content === 'video' ? 'video' : 'carousel');
    if (DRY_RUN) log(`\nDRY RUN — ${content} in ${lang}, platforms: ${platforms.join(', ')}\n`);
    for (const p of platforms) {
      if (p === 'facebook') {
        if (content === 'video') await postFacebookVideo(caption);
        else if (content === 'story') await postFacebookVideoStory();
        else await postFacebookCarousel(caption);
      } else if (p === 'instagram') {
        if (content === 'video') await postInstagramReel(caption);
        else if (content === 'story') await postInstagramStory();
        else await postInstagramCarousel(caption);
      } else if (p === 'linkedin') {
        if (content === 'story') die('LinkedIn stories are not supported by the API.');
        await postLinkedIn({
          caption,
          images: content === 'video' ? [] : carouselImages(),
          video: content === 'video' ? reelVideo() : null,
        });
      } else {
        die(`Unknown platform "${p}". Use facebook, instagram, linkedin, or all.`);
      }
    }
    log('');
  } else {
    log(`
Dukkani social publisher

  node social/publish.mjs whoami
  node social/publish.mjs captions [--content carousel|video] [--lang ar|en]
  node social/publish.mjs stage    [--content carousel|video|all]
  node social/publish.mjs post     [--platform all|facebook,instagram,linkedin]
                                   [--content carousel|video|story] [--lang ar|en] [--dry-run]
`);
  }
} catch (err) {
  die(err?.stack || String(err));
}
