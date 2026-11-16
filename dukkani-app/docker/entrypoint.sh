#!/usr/bin/env bash

set -euo pipefail

cd /var/www/html

# Progress lines go to stdout so Render's deploy log shows exactly which boot
# step is running. Never print APP_KEY or other secrets here.
log() {
    printf '[dukkani %s] %s\n' "$(date -u +%H:%M:%S)" "$*"
}

PORT="${PORT:-8080}"
export PORT

if ! [[ "${PORT}" =~ ^[0-9]+$ ]]; then
    log "invalid PORT value: '${PORT}'"
    exit 1
fi

log "entrypoint start (uid=$(id -u), port=${PORT})"

# Render passes the public URL and the port the router expects.
if [[ -z "${APP_URL:-}" && -n "${RENDER_EXTERNAL_URL:-}" ]]; then
    export APP_URL="${RENDER_EXTERNAL_URL}"
fi

# Apache's config substitution keeps every listener on Render's port.
sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost \*:${PORT}>/" /etc/apache2/sites-available/000-default.conf
log "apache configured for port ${PORT}"

DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export DB_CONNECTION
log "database driver: ${DB_CONNECTION}"

mkdir -p \
    bootstrap/cache \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs
log "storage directories ready"

case "${DB_CONNECTION}" in
    sqlite)
        # SQLite lives in the container. With a persistent disk it survives
        # restarts; on the free plan it is recreated and seeded on every boot.
        DB_DATABASE="${DB_DATABASE:-/var/data/database.sqlite}"
        export DB_DATABASE
        SQLITE_DIR="$(dirname "${DB_DATABASE}")"
        log "sqlite database: ${DB_DATABASE} (existing=$([[ -s "${DB_DATABASE}" ]] && echo yes || echo no))"

        mkdir -p "${SQLITE_DIR}" /var/data/public
        log "sqlite directory ready: $(df -h "${SQLITE_DIR}" 2>/dev/null | tail -1 | tr -s ' ')"

        # Uploads are served from the persistent disk through public/storage.
        if [[ -L storage/app/public ]]; then
            rm -f storage/app/public
        elif [[ -d storage/app/public ]]; then
            rm -rf storage/app/public
        fi
        ln -s /var/data/public storage/app/public
        log "public storage linked to /var/data/public"
        ;;
    pgsql|mysql)
        # The database is external. No SQLite file, demo seed, or uploads on
        # the ephemeral container disk.
        log "external database configured"
        ;;
    *)
        printf 'Unsupported DB_CONNECTION: %s\n' "${DB_CONNECTION}" >&2
        exit 1
        ;;
esac

# A stable APP_KEY survives restarts on the same disk. Production requires
# APP_KEY from the environment because the container filesystem is ephemeral.
if [[ "${DB_CONNECTION}" == "sqlite" && -z "${APP_KEY:-}" ]]; then
    if [[ -f /var/data/app.key ]]; then
        APP_KEY="$(cat /var/data/app.key)"
        log "app key loaded from disk"
    else
        APP_KEY="base64:$(head -c 32 /dev/urandom | base64 | tr -d '\n')"
        printf '%s' "${APP_KEY}" > /var/data/app.key
        chmod 600 /var/data/app.key
        log "app key generated"
    fi
fi

if [[ -z "${APP_KEY:-}" ]]; then
    printf 'APP_KEY is required when DB_CONNECTION=%s.\n' "${DB_CONNECTION}" >&2
    exit 1
fi

export APP_KEY

# Laravel requires a base64-encoded 32-byte key. A malformed key fails at
# runtime, so reject it before migrations or caches are built.
if ! APP_KEY="${APP_KEY}" php -r '
    $key = getenv("APP_KEY") ?: "";
    $decoded = str_starts_with($key, "base64:")
        ? base64_decode(substr($key, 7), true)
        : false;
    exit($decoded !== false && strlen($decoded) === 32 ? 0 : 1);
'; then
    printf '%s\n' "APP_KEY is invalid: expected base64: followed by exactly 32 decoded bytes." >&2
    exit 1
fi
log "app key validated"

# Clear stale config caches before migrating so env changes on a restarted
# container are picked up.
php artisan config:clear
log "config cache cleared"

case "${DB_CONNECTION}" in
    sqlite)
        php artisan storage:link --force
        log "storage link refreshed"

        if [[ ! -s "${DB_DATABASE}" ]]; then
            touch "${DB_DATABASE}"
            log "running migrations on a fresh database"
            php artisan migrate --force
            log "migrations finished"

            if [[ "${DEMO_SEED:-true}" == "true" ]]; then
                log "seeding demo data"
                php artisan db:seed --force
                log "demo seed finished"
            fi
        else
            log "running migrations on an existing database"
            php artisan migrate --force
            log "migrations finished"
        fi
        ;;
    pgsql|mysql)
        log "running migrations"
        php artisan migrate --force
        log "migrations finished"
        ;;
esac

log "caching config, routes, and views"
php artisan config:cache
php artisan route:cache
php artisan view:cache
log "caches built"

if [[ "${DB_CONNECTION}" == "sqlite" ]]; then
    chown -R www-data:www-data /var/data
fi

chown -R www-data:www-data storage bootstrap/cache
log "permissions updated"

log "starting $* on 0.0.0.0:${PORT}"
exec "$@"
