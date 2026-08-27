#!/usr/bin/env sh
set -eu

PORT="${PORT:-10000}"

cat > /etc/apache2/ports.conf <<EOF
Listen 0.0.0.0:${PORT}
EOF

sed -ri "s#<VirtualHost \*:[0-9]+>#<VirtualHost *:${PORT}>#g" /etc/apache2/sites-available/000-default.conf
sed -ri "s#DocumentRoot .*#DocumentRoot /var/www/html/public#g" /etc/apache2/sites-available/000-default.conf

echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf
a2enconf servername >/dev/null

echo "PORT=${PORT}"
echo "Apache ports.conf:"
cat /etc/apache2/ports.conf
echo "Apache enabled default virtual host:"
cat /etc/apache2/sites-enabled/000-default.conf

mkdir -p \
    storage/app/public \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

php artisan storage:link >/dev/null 2>&1 || true
php artisan optimize:clear

if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
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

echo "Starting Apache on 0.0.0.0:${PORT}"
grep -R "Listen" /etc/apache2/ports.conf
grep -E "VirtualHost|DocumentRoot" /etc/apache2/sites-available/000-default.conf
apache2ctl -S
echo "Listening sockets before Apache foreground:"
ss -ltnp || netstat -ltnp || true

(
    sleep 2
    echo "Listening sockets after Apache foreground start:"
    ss -ltnp || netstat -ltnp || true
) &

exec apache2-foreground
