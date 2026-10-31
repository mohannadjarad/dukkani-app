# Deploying the Dukkani demo to Render

The demo runs as a single Docker web service on [Render](https://render.com). The
shipped Blueprint targets Render's **free plan**, so no billing is required:
SQLite lives in the container, which is ephemeral, and the entrypoint reseeds the
demo whenever the database is empty. Everything needed is in the repository:

| File | Purpose |
|---|---|
| `render.yaml` (repo root) | Render Blueprint: service, health check, environment |
| `dukkani-app/Dockerfile` | Multi-stage image: Vite assets → Composer vendor → Apache + PHP |
| `dukkani-app/docker/entrypoint.sh` | Storage setup, `APP_KEY`, migrations, demo seed, caches |
| `dukkani-app/docker/apache.conf` | Apache vhost pointing at `public/` |
| `dukkani-app/docker/php.ini` | OpCache, upload limits, production flags |
| `dukkani-app/.env.production.example` | Reference for the production environment |

The image builds the front-end with Node, the PHP dependencies with Composer, and
runs the app with Apache and PHP 8.4. At boot the entrypoint prepares storage,
runs migrations, seeds the demo once on an empty database, caches config, routes
and views, then starts Apache on Render's `$PORT`.

## 1. Push the repository

Render deploys from Git. The Blueprint expects the repository root to contain the
`dukkani-app/` directory (the parent project layout in this repository).

```bash
git add -A
git commit -m "Dukkani demo"
git push
```

> If you publish `dukkani-app/` as its own repository, move `render.yaml` into it
> and delete the `rootDir`, `dockerfilePath` and `dockerContext` keys.

## 2. Create the Blueprint

The fastest path is the one-click link, which prefills the flow with the repo:

```text
https://render.com/deploy?repo=https://github.com/mohannadjarad/dukkani-app
```

Or do it manually:

1. Open the [Render Dashboard](https://dashboard.render.com) → **New +** → **Blueprint**.
2. Connect the GitHub repository that contains `render.yaml`.
3. Apply the Blueprint. Render creates the `dukkani-demo` web service and builds
   the image.
4. The first deploy finishes with a boot log like:

   ```text
   INFO  Running migrations.
   INFO  Seeding database.
   INFO  Caching framework bootstrap, configuration, metadata.
   ```

5. Open the service URL. Render names it after the service, so it is
   `https://dukkani-demo.onrender.com`. Log in with `demo@dukkani.jo` /
   `demo1234`, or use **دخول سريع إلى لوحة التحكم**.

The reference deployment is live at **https://dukkani-demo.onrender.com**.

> Blueprints can only be **created** from the Render Dashboard; the Render API
> exposes list / validate / update / delete only. You can also validate a
> `render.yaml` before pushing:
>
> ```bash
> curl -s -X POST https://api.render.com/v1/blueprints/validate \
>   -H "Authorization: Bearer $RENDER_API_KEY" \
>   -F "ownerId=$RENDER_OWNER_ID" \
>   -F "file=@render.yaml;type=application/x-yaml"
> ```

## Environment

The Blueprint sets all of these. You normally change nothing.

| Key | Value | Why |
|---|---|---|
| `APP_ENV` / `APP_DEBUG` | `production` / `false` | Real production settings |
| `APP_LOCALE` / `APP_TIMEZONE` | `ar` / `Asia/Amman` | Arabic UI, Amman business hours |
| `DEMO_MODE` | `true` | One-click demo login and sample-data labels |
| `DEMO_SEED` | `true` | Seed an empty database on first boot |
| `DB_DATABASE` | `/var/data/database.sqlite` | SQLite (persistent only when a disk is mounted) |
| `DB_JOURNAL_MODE` / `DB_BUSY_TIMEOUT` | `WAL` / `5000` | Concurrent dashboard + storefront reads |
| `SESSION_DRIVER` / `CACHE_STORE` | `database` | No external cache needed |
| `QUEUE_CONNECTION` | `sync` | The demo sends no queued work |
| `LOG_CHANNEL` | `stderr` | Logs appear in Render's log stream |
| `SESSION_SECURE_COOKIE` | `true` | HTTPS-only session cookie |

`APP_URL` is taken from Render's `RENDER_EXTERNAL_URL`. `APP_KEY` is generated
once by the entrypoint and stored at `/var/data/app.key` (the disk when one is
mounted, otherwise the ephemeral container). To manage the key yourself, add
`APP_KEY` (with the `base64:` prefix) to the service's environment variables.

## Persistence upgrade (paid)

On the free plan the container filesystem is ephemeral: every deploy, restart, or
wake-from-sleep begins empty, and the entrypoint reseeds it. Data created in the
dashboard is lost on the next restart. A persistent disk requires a paid instance
type and billing information on the workspace.

To switch to a disk:

1. Add a payment method: **Workspace Settings → Billing**.
2. In `render.yaml`, set `plan` to a paid instance type and restore the disk:

   ```yaml
   plan: starter
   disk:
     name: dukkani-data
     mountPath: /var/data
     sizeGB: 1
   ```

   Do **not** add `maxShutdownDelaySeconds` to a service that mounts a disk —
   Render rejects the Blueprint with `max shutdown delay is not supported for
   services with a disk`.
3. Push. The Blueprint re-syncs and the service restarts on the disk.

## Updating the demo

With `autoDeployTrigger: commit`, every push to `main` rebuilds and redeploys.
Migrations run on each boot; the demo seed runs only when the database file is
missing or empty.

To reset the demo data on a paid service, open the service **Shell** and run:

```bash
php artisan migrate:fresh --seed
```

## Local Docker test

If you have Docker installed, the same image runs locally:

```bash
cd dukkani-app
docker build -t dukkani-demo .
docker run --rm -p 8080:8080 \
  -e PORT=8080 \
  -e APP_ENV=production \
  -e APP_DEBUG=false \
  -e DB_DATABASE=/var/data/database.sqlite \
  -e SESSION_SECURE_COOKIE=false \
  -v dukkani-data:/var/data \
  dukkani-demo
# open http://localhost:8080
```

The named volume keeps the database and uploads between runs, mirroring a paid
Render disk. Keep `SESSION_SECURE_COOKIE=false` locally because local HTTP is not
HTTPS.

## Troubleshooting

| Symptom | Fix |
|---|---|
| **`need_payment_info` on validate/apply** | A disk or paid plan requires billing. Add a payment method, or use the shipped free plan. |
| **`lstat .../dukkani-app/dukkani-app: no such file or directory`** | With `rootDir` set, `dockerContext`/`dockerfilePath` are relative to it. Use `dockerContext: .` and `dockerfilePath: ./Dockerfile`. |
| **`maxShutdownDelaySeconds` not supported** | Remove it from any service that mounts a disk. |
| **502 / service not reaching the port** | The image starts Apache on `$PORT`; confirm Render sets `PORT` (it does by default). Check the boot log for the `Listen` line. |
| **`database is locked`** | Keep `DB_JOURNAL_MODE=WAL` and `DB_BUSY_TIMEOUT=5000`; the demo is a single instance. |
| **Health check fails** | `/up` is Laravel's health route. If it fails, check the container log for a PHP fatal error before Apache starts. |
| **Styles or fonts missing** | The image builds with `npm run build`; make sure the build step succeeded. Fonts are self-hosted and hashed by Vite. |
| **Data resets / lost `APP_KEY`** | Expected on the free plan (ephemeral filesystem). Add a disk to persist the database and key. |
| **Slow first request** | Free services sleep after 15 minutes idle and cold-start in about a minute. Use a paid instance for always-on. |

## Custom domain

Add the domain in **Settings → Custom Domains**, point the DNS records Render
shows you, and Render issues the TLS certificate automatically. No app change is
needed: `APP_URL` follows `RENDER_EXTERNAL_URL`, and the proxy headers are
already trusted in `bootstrap/app.php`.
