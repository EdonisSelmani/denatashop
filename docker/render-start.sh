#!/usr/bin/env sh
set -eu

PORT="${PORT:-10000}"

sed -ri "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost \*:.*>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

php artisan storage:link >/dev/null 2>&1 || true
php artisan optimize:clear

if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    php artisan migrate --force
fi

if [ "${RUN_CATALOG_IMPORT_DRY_RUN:-false}" = "true" ]; then
    php artisan catalog:deploy-to-production --dry-run
elif [ "${RUN_CATALOG_IMPORT:-false}" = "true" ]; then
    php artisan catalog:deploy-to-production
fi

php artisan config:cache

if ! php artisan route:cache; then
    echo "Route cache failed; continuing without cached routes." >&2
    php artisan route:clear || true
fi

php artisan view:cache

exec apache2-foreground
