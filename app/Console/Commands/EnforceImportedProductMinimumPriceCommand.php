<?php

namespace App\Console\Commands;

use App\Services\ImportedProductBatchActivator;
use Illuminate\Console\Command;
use Throwable;

class EnforceImportedProductMinimumPriceCommand extends Command
{
    protected $signature = 'catalog:enforce-imported-minimum-price
        {manifest-sha256 : SHA-256 recorded by the approved v2 manifest importer}
        {--expected=537 : Exact approved batch size}
        {--execute : Snapshot and raise only selling prices below EUR 1.50}';

    protected $description = 'Safely enforce the EUR 1.50 selling-price floor on one imported manifest batch.';

    public function handle(ImportedProductBatchActivator $activator): int
    {
        try {
            $hash = strtolower((string) $this->argument('manifest-sha256'));
            $expected = filter_var($this->option('expected'), FILTER_VALIDATE_INT);
            if ($expected === false || $expected < 1) {
                $this->error('--expected must be a positive whole number.');

                return self::FAILURE;
            }

            $inspection = $activator->inspect($hash, $expected);
            $this->table(['Check', 'Value'], [
                ['Matched products', $inspection['count']],
                ['Active products', $inspection['products']->where('is_active', true)->count()],
                ['Products with stock 10', $inspection['stock_10_count']],
                ['Selling prices below EUR 1.50', $inspection['below_minimum_count']],
            ]);

            if (! $this->option('execute')) {
                $this->info('Inspection complete. Nothing was changed.');

                return self::SUCCESS;
            }

            $result = $activator->enforceMinimumSellingPrice($hash, $expected);
            $this->info("Updated {$result['changed_prices']} of {$result['count']} imported product selling prices.");
            $this->line("Snapshot: {$result['snapshot_path']}");
            foreach ($result['examples'] as $example) {
                $this->line("{$example['sku']}: {$example['before']} -> {$example['after']}");
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
