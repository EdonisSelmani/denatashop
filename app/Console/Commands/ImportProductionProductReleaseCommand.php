<?php

namespace App\Console\Commands;

use App\Services\ProductionProductReleaseImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportProductionProductReleaseCommand extends Command
{
    protected $signature = 'catalog:import-production-release
        {--execute : Insert the reviewed release; without this flag the command is read-only}
        {--artifact=database/releases/denatashop-products-537-v1.json : Approved immutable product artifact}
        {--asset-manifest=database/releases/denatashop-products-537-v1-assets.json : Approved immutable asset manifest}';

    protected $description = 'Validate, dry-run, or explicitly insert the immutable 537-product production release.';

    public function handle(ProductionProductReleaseImporter $importer): int
    {
        $execute = (bool) $this->option('execute');
        $artifact = (string) $this->option('artifact');
        $assets = (string) $this->option('asset-manifest');

        try {
            $this->line($execute ? 'Mode: EXECUTE' : 'Mode: DRY RUN (default; no writes)');
            $this->line('Database: '.config('database.default').' / '.config('database.connections.'.config('database.default').'.database'));
            $plan = $importer->plan($artifact, $assets);
            $this->table(['Check', 'Count'], [
                ['Artifact products', $plan['product_count']],
                ['Artifact categories', $plan['category_count']],
                ['Artifact subcategories', $plan['subcategory_count']],
                ['Product images', $plan['unique_image_count']],
                ['Thumbnails', $plan['unique_thumbnail_count']],
                ['Categories to create', count($plan['categories_to_create'])],
                ['Subcategories to create', count($plan['subcategories_to_create'])],
                ['Products to insert', count($plan['products_to_insert'])],
                ['Expected existing batch', count($plan['already_existing_batch'])],
                ['Category conflicts', count($plan['category_conflicts'])],
                ['Subcategory conflicts', count($plan['subcategory_conflicts'])],
                ['SKU conflicts', count($plan['sku_conflicts'])],
                ['Supplier-code conflicts', count($plan['supplier_code_conflicts'])],
            ]);
            $this->line('Product artifact SHA-256: '.$plan['artifact_sha256']);
            $this->line('Asset manifest SHA-256: '.$plan['asset_manifest_sha256']);

            foreach (['category_conflicts', 'subcategory_conflicts', 'sku_conflicts', 'supplier_code_conflicts'] as $key) {
                foreach (array_slice($plan[$key], 0, 20) as $conflict) {
                    $this->warn($conflict);
                }
                if (count($plan[$key]) > 20) {
                    $this->warn('... '.(count($plan[$key]) - 20).' more '.$key);
                }
            }

            if ($plan['has_conflicts']) {
                $this->error('Conflicts require review. Nothing was changed.');

                return self::FAILURE;
            }

            if (! $execute) {
                $this->info('Dry run complete. All artifacts are valid and no database rows were changed.');

                return self::SUCCESS;
            }

            $result = $importer->execute($artifact, $assets);
            $this->table(['Result', 'Count'], collect($result)->map(fn ($count, $key) => [$key, $count])->values()->all());
            $this->info('Production release import completed safely.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
