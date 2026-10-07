<?php

namespace App\Console\Commands;

use App\Services\ImportedProductBatchActivator;
use Illuminate\Console\Command;
use Throwable;

class ActivateImportedProductBatchCommand extends Command
{
    protected $signature = 'catalog:activate-imported-batch
        {manifest-sha256 : SHA-256 recorded by the approved v2 manifest importer}
        {--expected=537 : Exact approved batch size}
        {--execute : Snapshot, normalize selling prices and activate the batch}';

    protected $description = 'Safely inspect or activate one exact manifest-provenance product batch.';

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
                ['Inactive products', $inspection['inactive_count']],
                ['Products with stock 10', $inspection['stock_10_count']],
                ['Selling prices requiring normalization', $inspection['price_change_count']],
            ]);

            if (! $this->option('execute')) {
                $this->info('Inspection complete. Nothing was changed.');

                return self::SUCCESS;
            }

            $result = $activator->activate($hash, $expected);
            $this->info("Activated {$result['count']} products; normalized {$result['changed_prices']} selling prices.");
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
