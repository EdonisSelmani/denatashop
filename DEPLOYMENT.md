# Deployment Checklist

Use this checklist before publishing Denataa Shop.

## Environment

1. Copy `.env.production.example` to `.env` on the server.
2. Set `APP_URL` to the real domain.
3. Generate a key:

```bash
php artisan key:generate
```

4. Keep these production values:

```env
APP_ENV=production
APP_DEBUG=false
DEBUGBAR_ENABLED=false
SESSION_ENCRYPT=true
FILESYSTEM_DISK=public
LOG_LEVEL=warning
```

## Install

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Admin

Create or promote an admin user:

```bash
php artisan admin:make admin@example.com
```

## After Deploy

1. Test registration and login.
2. Add a product to cart.
3. Place a test cash-on-delivery order.
4. Confirm the order appears in the admin panel.
5. Update the order status.
6. Confirm product images load from `/storage`.
7. Confirm emails are sent if SMTP is configured.

## Backups

Schedule daily backups for:

- Database
- `storage/app/public`
- `.env`

Never commit the real `.env` file.
