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

echo "Starting Apache on 0.0.0.0:${PORT}"

(
    sleep 2
    echo "PORT=${PORT}"
    echo "Apache ports.conf:"
    cat /etc/apache2/ports.conf
    echo "Apache enabled default virtual host:"
    cat /etc/apache2/sites-enabled/000-default.conf
    apache2ctl -S
    echo "Listening sockets after Apache foreground start:"
    ss -ltnp || netstat -ltnp || true
) &

exec apache2-foreground
