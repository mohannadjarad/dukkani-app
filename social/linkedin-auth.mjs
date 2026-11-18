#!/usr/bin/env node
// One-time LinkedIn OAuth helper: gets you a w_member_social access token.
//
//   1. In your LinkedIn app (linkedin.com/developers → Auth tab) add the
//      redirect URL exactly:  http://localhost:3000/callback
//   2. Put LINKEDIN_CLIENT_ID and LINKEDIN_CLIENT_SECRET in social/.env
//   3. Run:  node social/linkedin-auth.mjs
//   4. Open the printed URL, approve, and copy the token it prints into
//      LINKEDIN_ACCESS_TOKEN in social/.env.
//
// The token lasts ~60 days; re-run this when it expires.

import { createServer } from 'node:http';
import { readFileSync, writeFileSync, existsSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const HERE = path.dirname(fileURLToPath(import.meta.url));
const ROOT = path.resolve(HERE, '..');
const ENV_FILE = path.join(HERE, '.env');

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

/** Write/overwrite KEY=VALUE lines in social/.env, appending any that are new. */
function writeEnv(updates) {
  if (!existsSync(ENV_FILE)) return;
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
    out.push('# Added by social/linkedin-auth.mjs');
    for (const [k, v] of missing) out.push(`${k}=${v}`);
  }
  writeFileSync(ENV_FILE, out.join('\n'));
}

const CLIENT_ID = process.env.LINKEDIN_CLIENT_ID?.trim();
const CLIENT_SECRET = process.env.LINKEDIN_CLIENT_SECRET?.trim();
const PORT = 3000;
const REDIRECT = `http://localhost:${PORT}/callback`;
const SCOPES = 'openid profile w_member_social';

if (!CLIENT_ID || !CLIENT_SECRET) {
  console.error('Set LINKEDIN_CLIENT_ID and LINKEDIN_CLIENT_SECRET in social/.env first.');
  process.exit(1);
}

const authUrl =
  'https://www.linkedin.com/oauth/v2/authorization?' +
  new URLSearchParams({
    response_type: 'code',
    client_id: CLIENT_ID,
    redirect_uri: REDIRECT,
    scope: SCOPES,
  }).toString();

console.log('\nOpen this URL in your browser and approve access:\n');
console.log(authUrl + '\n');
console.log(`Waiting for the redirect to ${REDIRECT} …\n`);

const server = createServer(async (req, res) => {
  const url = new URL(req.url, `http://localhost:${PORT}`);
  if (url.pathname !== '/callback') {
    res.writeHead(404).end();
    return;
  }
  const code = url.searchParams.get('code');
  const error = url.searchParams.get('error');
  if (error || !code) {
    res.writeHead(400, { 'Content-Type': 'text/plain' }).end(`Error: ${error || 'no code'}`);
    console.error('Authorization failed:', error);
    server.close();
    return;
  }

  try {
    const tokenRes = await fetch('https://www.linkedin.com/oauth/v2/accessToken', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({
        grant_type: 'authorization_code',
        code,
        redirect_uri: REDIRECT,
        client_id: CLIENT_ID,
        client_secret: CLIENT_SECRET,
      }),
    });
    const json = await tokenRes.json();
    if (!tokenRes.ok || !json.access_token) {
      throw new Error(json.error_description || JSON.stringify(json));
    }

    res
      .writeHead(200, { 'Content-Type': 'text/html' })
      .end('<h2>Done — you can close this tab and go back to the terminal.</h2>');

    const updates = { LINKEDIN_ACCESS_TOKEN: json.access_token };
    try {
      const info = await fetch('https://api.linkedin.com/v2/userinfo', {
        headers: { Authorization: `Bearer ${json.access_token}` },
      }).then((r) => r.json());
      if (info.sub) updates.LINKEDIN_AUTHOR_URN = `urn:li:person:${info.sub}`;
    } catch {}
    writeEnv(updates);

    console.log('Success! Saved to social/.env:\n');
    console.log(`LINKEDIN_ACCESS_TOKEN=${json.access_token}`);
    if (updates.LINKEDIN_AUTHOR_URN) console.log(`LINKEDIN_AUTHOR_URN=${updates.LINKEDIN_AUTHOR_URN}`);
    if (json.expires_in) {
      const days = Math.round(json.expires_in / 86400);
      console.log(`# expires in ~${days} days`);
    }
    console.log('');
  } catch (err) {
    res.writeHead(500, { 'Content-Type': 'text/plain' }).end(String(err));
    console.error('Token exchange failed:', err.message);
  } finally {
    server.close();
  }
});

server.listen(PORT, () => {});
