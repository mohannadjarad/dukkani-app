# Deploying Dukkani

Dukkani runs as two Render web services built from `render.yaml`:

| Service | Purpose | Database | Files |
|---|---|---|---|
| `dukkani-demo` | Public demo with seeded data | SQLite on the container (ephemeral) | Local demo assets |
| `dukkani-prod` | Production pilot | Neon Postgres | Cloudinary |

Both run on Render's **free** plan. No paid add-on, no credit card, no Render
disk, and no S3 bucket are required.

## Free-tier components

| Component | Service | Notes |
|---|---|---|
| App | Render Free, Frankfurt | 512 MB, 750 instance-hours per workspace per month |
| Database | Neon Free, AWS `eu-central-1` (Frankfurt) | 1 GB, direct connection |
| Images | Cloudinary Free | 25 credits, `f_auto,q_auto` delivery |
| Backups | GitHub Actions artifacts | Daily `pg_dump`, 90-day retention |
| Keep-alive | cron-job.org | `/up` every 10 minutes, 06:00–20:00 UTC |
| Domain | `*.onrender.com` | Free Render subdomain |

> The 750 hours are shared by the whole workspace. Never add a keep-alive
> pinger to `dukkani-demo`; only ping `dukkani-prod`.

## 1. Neon Postgres

1. Create a [Neon](https://neon.tech) project named `dukkani-prod`.
2. Select region **AWS eu-central-1 (Frankfurt)**.
3. Keep the default database `neondb` and role `neondb_owner`.
4. Open **Connection Details** and copy the **direct** connection string, not
   the pooled one. The host looks like:
   `ep-xxxx.aws-eu-central-1.aws.neon.tech`.
5. Split the string into the Render variables:

   ```env
   DB_CONNECTION=pgsql
   DB_HOST=ep-xxxx.aws-eu-central-1.aws.neon.tech
   DB_PORT=5432
   DB_DATABASE=neondb
   DB_USERNAME=neondb_owner
   DB_PASSWORD=...
   DB_SSLMODE=require
   ```

   Alternatively, set `DB_URL` to the full connection string. `DB_URL` takes
   precedence over the individual `DB_*` values.

### `channel_binding`

Neon connection strings sometimes include `channel_binding=require`. If the
container logs a PDO connection error that mentions `channel_binding`, remove
that query parameter from the URL and keep `sslmode=require`. Individual
`DB_*` variables never include it.

Migrations run automatically inside the Render container on every boot. Local
PHP does not need `pdo_pgsql`; the Docker image installs it.

## 2. Cloudinary

1. Create a free [Cloudinary](https://cloudinary.com) account.
2. Open **Console → API Keys** and copy the **API environment variable**:
   `cloudinary://API_KEY:API_SECRET@CLOUD_NAME`.
3. Store it as `CLOUDINARY_URL` in Render. Never commit it.

Delivery URLs are generated with `f_auto,q_auto,w_800`; product thumbnails use
`f_auto,q_auto,w_400`. Replacing or deleting an image removes the old Cloudinary
asset.

`dukkani-demo` has no Cloudinary account. Because `DEMO_MODE=true`, its uploads
keep using the container disk through the local fallback, exactly as before.

## 3. GitHub secret for backups

In the GitHub repository: **Settings → Secrets and variables → Actions → New
repository secret**.

- Name: `NEON_URL`
- Value: the full Neon direct connection string.

The daily workflow `.github/workflows/backup.yml` reads this secret and never
prints it.

## 4. Apply the Render Blueprint

1. Open the [Render Dashboard](https://dashboard.render.com) → **New +** →
   **Blueprint**.
2. Connect the repository that contains `render.yaml` at its root.
3. Apply the Blueprint. Render creates or updates both services:
   - `dukkani-demo` (unchanged, SQLite)
   - `dukkani-prod` (Docker, Frankfurt, free, `/up` health check)
4. Render prompts for every `sync: false` value on `dukkani-prod`:

   | Variable | Value |
   |---|---|
   | `APP_KEY` | One-time key, see below |
   | `DB_HOST` | Neon direct host |
   | `DB_PORT` | `5432` |
   | `DB_DATABASE` | `neondb` |
   | `DB_USERNAME` | `neondb_owner` |
   | `DB_PASSWORD` | Neon password |
   | `DB_SSLMODE` | `require` |
   | `CLOUDINARY_URL` | Full Cloudinary URL |
   | `DUKKANI_SUPPORT_WHATSAPP` | Platform support number, international format without `+` |
   | `DEFAULT_STORE_SLUG` | Leave blank until the first real store exists, then set it to the store slug |

   `DUKKANI_SUPPORT_WHATSAPP` powers the upgrade CTAs and the "store is full"
   page. Until it is set, the buttons fall back to the placeholder in
   `config/plans.php`; set it before launch.

5. Generate `APP_KEY` **once** and paste it. The value must be `base64:`
   followed by exactly 32 decoded bytes:

   ```bash
   php -r "echo 'base64:'.base64_encode(random_bytes(32)).PHP_EOL;"
   ```

6. Keep the key identical across restarts. A malformed key stops the container
   before migrations run. The entrypoint validates the prefix, the base64
   payload, and the decoded length.

> `APP_URL` is set to `https://dukkani-prod.onrender.com`. If the service name
> is taken and Render assigns a different URL, update `APP_URL` and the pinger
> URL.

## 5. First boot

Watch the `dukkani-prod` logs. A healthy first deploy shows:

```text
INFO  Running migrations.
INFO  Caching framework bootstrap, configuration, and metadata.
```

Then verify:

- `https://dukkani-prod.onrender.com/up` returns HTTP 200.
- `/login` renders.
- No demo data exists; production has `DEMO_MODE=false` and `DEMO_SEED=false`.
- Do **not** create the customer store until the M1 exit gate passes.

### Creating the first account

There is no self-serve registration yet, so accounts are created from the
command line:

```bash
cd dukkani-app
DB_CONNECTION=pgsql \
DB_HOST=ep-xxxx.aws-eu-central-1.aws.neon.tech \
DB_PORT=5432 \
DB_DATABASE=neondb \
DB_USERNAME=neondb_owner \
DB_PASSWORD='...' \
DB_SSLMODE=require \
php artisan dukkani:store:create
```

The command prompts for the store name, public slug, owner name, email,
password, phone, WhatsApp, city, and country, then creates the user, the store,
and seven opening hours in one transaction. It refuses duplicate emails or
slugs. A brand-new store also gets a free 7-day Starter trial.

For automation, pass `--store-name`, `--slug`, `--owner-name`, `--email`,
`--password`, `--phone`, `--whatsapp`, `--city`, and `--country-code`, then add
`--no-interaction`. `--country-code` defaults to `962`; pass `970` for
Palestine. `--currency` and `--timezone` default to the country's values (`ILS`
and `Asia/Hebron` for `970`; `JOD` and `Asia/Amman` for `962`). Add `--no-trial`
to skip the free trial and start on the Free limits.

### Granting platform-admin access

There is no admin UI to create the first admin, so promote an existing account:

```bash
php artisan dukkani:admin:grant owner@example.com
```

Add `--revoke` to remove access. Admins sign in and land on `/admin/stores`,
where they can change a store's plan, grant extra orders, or suspend a store.


For the M1 exit gate, create a **throwaway** account, verify login and image
uploads, then remove it:

```sql
DELETE FROM users WHERE email = 'gate-test@example.com';
```

`stores.owner_id` cascades, so the store and its opening hours go with it.

## 6. Keep-alive pinger

Render Free suspends after about 15 minutes of inactivity. Use
[cron-job.org](https://cron-job.org):

1. Create a job for `https://dukkani-prod.onrender.com/up`.
2. Schedule every **10 minutes** (`*/10 6-19 * * *`) in the **UTC** timezone,
   active **06:00–19:50 UTC**.
3. Set the request timeout to **30 seconds** (cron-job.org's maximum). The first
   ping after an overnight sleep can time out while Render cold-starts, but the
   request still wakes the service; the next ping succeeds. Set failure
   notifications to fire only after 2–3 consecutive failures.
4. Accept any 2xx response; the health route does not touch Postgres.
5. Do not create a second job for `dukkani-demo`.

A 14-hour daily window costs roughly 420 instance-hours per month, leaving room
for the demo inside the shared 750-hour workspace budget.

## 7. Backups and restore

### Automatic backup

`.github/workflows/backup.yml` runs every day at 02:00 UTC and can also be
started manually from the **Actions** tab. It writes a custom-format dump,
gzip-compresses it, and uploads it as a GitHub artifact for 90 days. The
workflow installs the newest PostgreSQL client so `pg_dump` is never older than
the Neon server major version.

### Restore into a fresh Neon branch

1. Download the artifact and extract the `.dump.gz` file.
2. In Neon, create a new branch from the production branch.
3. Copy the new branch's connection string and export it as `RESTORE_URL`.
4. Reset the target schema so the dump restores cleanly:

   ```bash
   psql "$RESTORE_URL" -c 'DROP SCHEMA public CASCADE; CREATE SCHEMA public;'
   ```

5. Restore:

   ```bash
   gunzip -c backup.dump.gz \
     | pg_restore --no-owner --no-privileges --dbname="$RESTORE_URL"
   ```

6. Verify counts and a sample row:

   ```bash
   psql "$RESTORE_URL" -c 'SELECT count(*) FROM stores;'
   psql "$RESTORE_URL" -c 'SELECT count(*) FROM products;'
   psql "$RESTORE_URL" -c 'SELECT count(*) FROM orders;'
   ```

7. Delete the test branch.

Neon also provides a 6-hour instant restore window and manual snapshots as a
first line of defence. Cloudinary assets are not part of the database backup;
the database stores their URLs and public IDs.

## 8. M1 exit gate

- [x] Migrations succeeded against Neon (22 tables).
- [x] Create a throwaway account with `dukkani:store:create`, log in, restart
      the service, and log in again. The account still exists. (Verified on
      2026-11-16: the session created before a redeploy and an API restart
      still opened `/dashboard` afterwards.)
- [x] Upload an image, restart the service: the image still loads. (Cloudinary
      URL returned 200 before and after the restart.)
- [x] Delete a product: the Cloudinary asset is removed. (Product soft-deleted;
      Cloudinary Admin API returned an empty resource list.)
- [x] `pg_dump` + `pg_restore` into a fresh Neon database succeeded. (178 dump
      objects restored into `dukkani_restore_test`; table and row counts matched
      production, then the database was dropped.)
- [x] `/up` returns 200 and the cron-job.org pinger reports success. (Job
      `Dukkani prod keep-warm` is live; Render's access log shows
      `GET /up` 200 from the `cron-job.org` user agent every 10 minutes.)
- [x] No reliance on local SQLite or container-local uploads in production.

## 9. Demo service

`dukkani-demo` is intentionally separate:

- SQLite file at `/var/data/database.sqlite` inside the ephemeral container.
- `DEMO_MODE=true` enables one-click login, sample labels, and the local image
  fallback (no Cloudinary account needed).
- `DEMO_SEED=true` seeds an empty database on first boot.
- No keep-alive pinger.
- Login: `demo@dukkani.jo` / `demo1234`, or **دخول سريع إلى لوحة التحكم**.
- Live at `https://dukkani-demo.onrender.com`.

If you ever add a paid disk, the Blueprint comments show the disk block; do not
add `maxShutdownDelaySeconds` to a service that mounts a disk.

## 10. Plans and admin panel

Plan limits live in `config/plans.php` and can be edited without code changes.
The three plans are Free (15 orders, 12 products, 1 user), Starter (unlimited),
and Pro (unlimited).

- A new store starts on a free 7-day Starter trial. When the trial ends the
  store drops to the Free limits automatically.
- `PlanService` checks expiry **in real time** on every relevant request, so the
  limits hold even without a scheduler. The `plans:expire` command is only a
  daily cleanup that rewrites the stored `stores.plan` column; it is scheduled
  in `routes/console.php` and does not need a running cron to be correct.
- A paid plan keeps working for a 7-day grace period after its expiry date,
  giving the owner time to renew.
- When a store hits its monthly order limit, checkout is blocked and the
  customer sees a friendly page with a WhatsApp button to the store and a second
  one to platform support. The owner sees warning banners at 12/15 and a red
  banner at 15/15.
- Customer log and analytics are locked on Free; the owner gets a 403 upgrade
  screen instead.

The admin panel at `/admin/stores` (admin users only) lists every store with its
owner, plan, monthly usage, and last login, and lets you change the plan,
grant extra orders (`order_limit_override`, added on top of the free base),
suspend, or reopen a store.

## 11. Local development

Local development stays on SQLite. The Docker image adds `pdo_pgsql`, but local
PHP does not need it.

```bash
cd dukkani-app
composer install
npm install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
npm run build
php artisan serve
```

Image uploads use Cloudinary when `CLOUDINARY_URL` is set. With an empty
`CLOUDINARY_URL`, uploads fall back to the `public` disk only in `local` and
`testing`. Any other environment, including production, returns a clear Arabic
validation error instead of storing the file on an ephemeral disk.

## 12. Troubleshooting

| Symptom | Cause / fix |
|---|---|
| `could not find driver pgsql` | The image was built before `pdo_pgsql` was added; redeploy from the latest commit. |
| PDO error mentioning `channel_binding` | Remove `channel_binding=require` from the Neon URL; keep `sslmode=require`. |
| `APP_KEY is invalid` in the boot log | The key must be `base64:` plus exactly 32 decoded bytes. |
| `APP_KEY is required` in the boot log | `dukkani-prod` started without an `APP_KEY`; set it in Render. |
| Image upload shows `خدمة الصور غير مهيأة` | `CLOUDINARY_URL` is missing or empty in that environment. |
| `pg_dump: server version ... pg_dump version` | The backup workflow pins the newest PostgreSQL client; re-run the job. |
| Health check fails | Inspect the boot log before Apache starts; usually a bad `APP_KEY` or database connection. |
| Demo data resets | Expected: `dukkani-demo` has no persistent disk. |
