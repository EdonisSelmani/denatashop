# Production Catalog Import

This import is intended to be run once on the Render staging/production service that uses Neon PostgreSQL.

## Required Backup

Before enabling the import, create a Neon backup point without committing or sharing credentials.

Preferred:

1. Open the Neon dashboard.
2. Create a branch, restore point, or backup from the current production database.
3. Confirm the backup/branch exists before changing Render environment variables.

Optional local export, if `pg_dump` is available and `DATABASE_URL` is already set securely in your shell:

```sh
pg_dump "$DATABASE_URL" --format=custom --no-owner --no-acl --file="denatashop-neon-before-catalog.dump"
```

For PowerShell:

```powershell
pg_dump $env:DATABASE_URL --format=custom --no-owner --no-acl --file denatashop-neon-before-catalog.dump
```

Do not commit the dump file. Do not print the database URL.

## Dry Run

Inside a production-like shell with PostgreSQL configured:

```sh
php artisan catalog:deploy-to-production --dry-run
```

For local verification only, where `APP_ENV` and `DB_CONNECTION` are not production PostgreSQL:

```sh
php artisan catalog:deploy-to-production --dry-run --allow-testing-override
```

## One-Time Render Run

Keep the default disabled value committed in `render.yaml`:

```text
RUN_CATALOG_IMPORT=false
RUN_CATALOG_IMPORT_DRY_RUN=false
```

For a read-only Render log check before the real import:

1. In Render, set `RUN_CATALOG_IMPORT_DRY_RUN=true`.
2. Trigger one manual deploy.
3. Watch logs for `Dry-run complete. No database rows were written.`
4. Set `RUN_CATALOG_IMPORT_DRY_RUN=false`.

When the backup exists and the dry-run output is approved:

1. In Render, set `RUN_CATALOG_IMPORT=true`.
2. Trigger one manual deploy.
3. Watch logs for `Production catalog deployment completed.`
4. Confirm catalog counts and product pages.
5. Set `RUN_CATALOG_IMPORT=false`.
6. Trigger another deploy or restart so future restarts do not run the import.

The command is idempotent and matches products by SKU. It does not delete products, users, orders, carts, favorites, or sessions.
