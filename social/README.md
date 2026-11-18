# Dukkani — social publishing

Publishes the kit in `dukkani-post/` (6-image carousel + voice reel) to
**Facebook**, **Instagram**, and **LinkedIn** through their official APIs.

Everything runs from one dependency-free Node script:

```bash
node social/publish.mjs captions --content carousel --lang ar   # preview copy
node social/publish.mjs stage    --content carousel             # copy images for hosting
node social/publish.mjs whoami                                  # check credentials
node social/publish.mjs post --platform all --content carousel --lang ar --dry-run
node social/publish.mjs post --platform all --content carousel --lang ar
```

Credentials go in `social/.env` (gitignored) — copy `social/.env.example` and fill
it in. Nothing in this folder is committed except `.env.example`.

---

## ✅ What you need to do (checklist)

### 1. Facebook Page + Instagram (one Meta app covers both)

1. Your Instagram must be a **Professional account** (Business or Creator) and
   **linked to a Facebook Page** you admin:
   Instagram app → Settings → Account type, and → linked accounts.
2. Create an app at <https://developers.facebook.com/apps> → **Create app** →
   type **Business**.
3. Add products **Facebook Login** and **Instagram Graph API**.
4. Open **Graph API Explorer**, pick your app, and grant these permissions to
   *your own* account: `pages_show_list`, `pages_read_engagement`,
   `pages_manage_posts`, `instagram_basic`, `instagram_content_publish`,
   `business_management`. Copy the generated **User Access Token** (it is
   short-lived — about an hour).
5. Copy the app's **App ID** / **App Secret** (app → Settings → Basic) and the
   token into `social/.env`:
   ```
   META_APP_ID=...
   META_APP_SECRET=...
   META_USER_ACCESS_TOKEN=...     # the short-lived token from step 4
   ```
6. Let the helper do the fiddly part — it swaps the short-lived token for a
   ~60-day one and auto-fills your Page id, Page token, and Instagram id:
   ```
   node social/meta-auth.mjs
   ```
   (Manually this is: `GET /me/accounts` for the Page id + token, then
   `GET /{page-id}?fields=instagram_business_account` for the IG id.)
7. Verify with `node social/publish.mjs whoami`.

> The pages your account admins are auto-detected; with more than one, set
> `META_PAGE_NAME=<exact page name>` in `social/.env` before running the helper.

> You can post to your **own** Page/IG in the app's Development mode — no App
> Review needed. App Review is only required to post on behalf of *other* people.

### 2. LinkedIn (personal profile)

1. Create an app at <https://www.linkedin.com/developers/apps>.
2. **Products** tab → request **Share on LinkedIn** (gives `w_member_social`).
   While the app is in Development mode this works for the app owner.
3. **Auth** tab → add redirect URL `http://localhost:3000/callback`.
4. Put the client id/secret in `social/.env`, then run:
   ```bash
   node social/linkedin-auth.mjs
   ```
   Open the printed URL, approve, and paste the printed token into
   `LINKEDIN_ACCESS_TOKEN` (and `LINKEDIN_AUTHOR_URN` if shown).

### 3. Host the carousel images (Instagram only)

Instagram downloads media from a **public URL** — it cannot read your disk.
Facebook and LinkedIn upload directly from disk, so this is IG-only.

```bash
node social/publish.mjs stage --content carousel
```

This copies `01.png`–`06.png` into `dukkani-landing/social/`. Deploy
`dukkani-landing`, then set in `social/.env`:

```
PUBLIC_MEDIA_BASE_URL=https://<your-landing-host>/social
```

(Or set `IG_IMAGE_URLS` to six comma-separated public URLs directly.)

### 4. Publish

```bash
# first a dry run — checks config, prints every request, posts nothing
node social/publish.mjs post --platform all --content carousel --lang ar --dry-run

# for real
node social/publish.mjs post --platform all --content carousel --lang ar
```

Language: `--lang ar` (Jordanian audience) or `--lang en` (LinkedIn/international).
Platforms: `facebook`, `instagram`, `linkedin`, or `all`.
Content: `carousel` (images) or `video` (the reel).

---

## What each platform does

| Platform | Carousel | Reel |
| --- | --- | --- |
| Facebook | uploads 6 unpublished photos, then one feed post with `attached_media` | uploads the MP4 to `/page/videos` |
| Instagram | 6 `is_carousel_item` containers → `CAROUSEL` container → `media_publish` | `REELS` container from the public video URL → `media_publish` |
| LinkedIn | `initializeUpload` + PUT each image → `/rest/posts` with `multiImage` | Videos API: `initializeUpload` → PUT each ~4 MB part → `finalizeUpload` → poll `AVAILABLE` → `/rest/posts` with `content.media` |

## Notes & gotchas

- **Tokens expire.** LinkedIn ~60 days; Meta long-lived Page token ~60 days.
  Re-run the relevant step when posting starts failing with HTTP 401/403.
- **Instagram rate limit:** 50 API posts / 24h per account; containers expire in
  24 hours.
- **Image sizes:** Facebook caps photos at 4 MB (the kit is ~0.5–0.9 MB, fine).
- Carousels need **2–20 images**; LinkedIn uses `multiImage` (the kit has 6).
- The captions are parsed straight from `dukkani-post/captions.md`, so editing
  that file changes what gets posted — no duplication.

## Files

| File | Purpose |
| --- | --- |
| `publish.mjs` | The publisher (`post`, `whoami`, `captions`, `stage`). |
| `linkedin-auth.mjs` | One-time LinkedIn OAuth token helper. |
| `meta-auth.mjs` | One-time Meta token exchange + Page/IG id auto-fill. |
| `.env.example` | Copy to `social/.env` and fill in. |
| `dukkani-post/captions.md` | Source of the copy posted by the script. |
