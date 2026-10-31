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

mkdir -p \
    bootstrap/cache \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs
chown -R www-data:www-data storage bootstrap/cache /var/data

# A stable APP_KEY survives restarts on the same disk. Set APP_KEY in Render
# if you prefer to manage it yourself.
if [[ -z "${APP_KEY:-}" ]]; then
    if [[ -f /var/data/app.key ]]; then
        APP_KEY="$(cat /var/data/app.key)"
    else
        APP_KEY="base64:$(head -c 32 /dev/urandom | base64 | tr -d '\n')"
        printf '%s' "${APP_KEY}" > /var/data/app.key
        chmod 600 /var/data/app.key
    fi
    export APP_KEY
fi

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

php artisan optimize --no-interaction

chown -R www-data:www-data /var/data storage bootstrap/cache

exec "$@"
