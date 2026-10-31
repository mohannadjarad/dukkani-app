# Deploying the Dukkani demo to Render

The demo runs as a single Docker web service on [Render](https://render.com) with
SQLite on a persistent disk. Everything needed is in the repository:

| File | Purpose |
|---|---|
| `render.yaml` (repo root) | Render Blueprint: service, disk, health check, environment |
| `dukkani-app/Dockerfile` | Multi-stage image: Vite assets → Composer vendor → Apache + PHP |
| `dukkani-app/docker/entrypoint.sh` | Disk setup, `APP_KEY`, migrations, demo seed, caches |
| `dukkani-app/docker/apache.conf` | Apache vhost pointing at `public/` |
| `dukkani-app/docker/php.ini` | OpCache, upload limits, production flags |
| `dukkani-app/.env.production.example` | Reference for the production environment |

The image builds the front-end with Node, the PHP dependencies with Composer, and
runs the app with Apache and PHP 8.4. At boot the entrypoint prepares the disk,
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

1. Open the [Render Dashboard](https://dashboard.render.com) → **New +** → **Blueprint**.
2. Connect the GitHub repository that contains `render.yaml`.
3. Apply the Blueprint. Render creates the `dukkani-demo` web service and the
   `dukkani-data` disk, then builds the image.
4. The first deploy finishes with a boot log like:

   ```text
   INFO  Running migrations.
   INFO  Seeding database.
   INFO  Caching framework bootstrap, configuration, metadata.
   ```

5. Open the service URL. Render names it after the service, so it is usually
   `https://dukkani-demo.onrender.com`. Log in with `demo@dukkani.jo` /
   `demo1234`, or use **دخول سريع إلى لوحة التحكم**.

## Environment

The Blueprint sets all of these. You normally change nothing.

| Key | Value | Why |
|---|---|---|
| `APP_ENV` / `APP_DEBUG` | `production` / `false` | Real production settings |
| `APP_LOCALE` / `APP_TIMEZONE` | `ar` / `Asia/Amman` | Arabic UI, Amman business hours |
| `DEMO_MODE` | `true` | One-click demo login and sample-data labels |
| `DEMO_SEED` | `true` | Seed an empty database on first boot |
| `DB_DATABASE` | `/var/data/database.sqlite` | SQLite on the persistent disk |
| `DB_JOURNAL_MODE` / `DB_BUSY_TIMEOUT` | `WAL` / `5000` | Concurrent dashboard + storefront reads |
| `SESSION_DRIVER` / `CACHE_STORE` | `database` | No external cache needed |
| `QUEUE_CONNECTION` | `sync` | The demo sends no queued work |
| `LOG_CHANNEL` | `stderr` | Logs appear in Render's log stream |
| `SESSION_SECURE_COOKIE` | `true` | HTTPS-only session cookie |

`APP_URL` is taken from Render's `RENDER_EXTERNAL_URL`. `APP_KEY` is generated
once by the entrypoint and stored at `/var/data/app.key`, so sessions survive
restarts. To manage the key yourself, add `APP_KEY` (with the `base64:` prefix)
to the service's environment variables.

## Free-tier option

A persistent disk requires a paid instance type. To run on Render's free plan,
open `render.yaml`, remove the `disk` block and set `plan: free`. The app still
works, but the container filesystem is ephemeral: every deploy, restart, or
wake-from-sleep starts from an empty database, and the entrypoint reseeds it.
Demo data created in the dashboard is lost on the next restart.

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

The named volume keeps the database and uploads between runs, mirroring Render's
disk. Keep `SESSION_SECURE_COOKIE=false` locally because local HTTP is not HTTPS.

## Troubleshooting

| Symptom | Fix |
|---|---|
| **502 / service not reaching the port** | The image starts Apache on `$PORT`; confirm Render sets `PORT` (it does by default). Check the boot log for the `Listen` line. |
| **Uploads disappear after deploy** | The disk must be mounted at `/var/data`; the entrypoint symlinks `storage/app/public` to `/var/data/public`. |
| **`database is locked`** | Keep `DB_JOURNAL_MODE=WAL` and `DB_BUSY_TIMEOUT=5000`; the demo is a single instance. |
| **Health check fails** | `/up` is Laravel's health route. If it fails, check the container log for a PHP fatal error before Apache starts. |
| **Styles or fonts missing** | The image builds with `npm run build`; make sure the build step succeeded. Fonts are self-hosted and hashed by Vite. |
| **Lost `APP_KEY`** | It lives on the disk at `/var/data/app.key`. If the disk is recreated, a new key is generated and existing sessions are invalidated (users just log in again). |

## Custom domain

Add the domain in **Settings → Custom Domains**, point the DNS records Render
shows you, and Render issues the TLS certificate automatically. No app change is
needed: `APP_URL` follows `RENDER_EXTERNAL_URL`, and the proxy headers are
already trusted in `bootstrap/app.php`.
