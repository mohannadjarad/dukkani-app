#!/usr/bin/env bash

set -euo pipefail

cd /var/www/html

PORT="${PORT:-8080}"
export PORT

# Render passes the public URL and the port the router expects.
if [[ -z "${APP_URL:-}" && -n "${RENDER_EXTERNAL_URL:-}" ]]; then
    export APP_URL="${RENDER_EXTERNAL_URL}"
fi

# Apache's config substitution keeps every listener on Render's port.
sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

DB_CONNECTION="${DB_CONNECTION:-sqlite}"
export DB_CONNECTION

mkdir -p \
    bootstrap/cache \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs

case "${DB_CONNECTION}" in
    sqlite)
        # SQLite lives on the persistent disk when one is mounted at /var/data.
        DB_DATABASE="${DB_DATABASE:-/var/data/database.sqlite}"
        export DB_DATABASE
        mkdir -p "$(dirname "${DB_DATABASE}")" /var/data/public

        # Uploads are served from the persistent disk through public/storage.
        if [[ -L storage/app/public ]]; then
            rm -f storage/app/public
        elif [[ -d storage/app/public ]]; then
            rm -rf storage/app/public
        fi
        ln -s /var/data/public storage/app/public
        ;;
    pgsql|mysql)
        # The database is external. No SQLite file, demo seed, or uploads on
        # the ephemeral container disk.
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
    else
        APP_KEY="base64:$(head -c 32 /dev/urandom | base64 | tr -d '\n')"
        printf '%s' "${APP_KEY}" > /var/data/app.key
        chmod 600 /var/data/app.key
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

# Clear stale config caches before migrating so env changes on a restarted
# container are picked up.
php artisan config:clear

case "${DB_CONNECTION}" in
    sqlite)
        php artisan storage:link --force

        if [[ ! -s "${DB_DATABASE}" ]]; then
            touch "${DB_DATABASE}"
            php artisan migrate --force

            if [[ "${DEMO_SEED:-true}" == "true" ]]; then
                php artisan db:seed --force
            fi
        else
            php artisan migrate --force
        fi
        ;;
    pgsql|mysql)
        php artisan migrate --force
        ;;
esac

php artisan config:cache
php artisan route:cache
php artisan view:cache

if [[ "${DB_CONNECTION}" == "sqlite" ]]; then
    chown -R www-data:www-data /var/data
fi

chown -R www-data:www-data storage bootstrap/cache

exec "$@"
