#!/usr/bin/env sh
set -eu

PORT="${PORT:-10000}"

mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ ! -L public/storage ] && [ ! -e public/storage ]; then
    ln -s ../storage/app/public public/storage
fi

echo "Starting PHP built-in server on 0.0.0.0:${PORT}"

exec php -S "0.0.0.0:${PORT}" -t public docker/php-server-router.php
