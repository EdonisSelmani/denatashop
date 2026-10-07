<?php

namespace App\Console\Commands;

use App\Services\ProductManifestImporter;
use Illuminate\Console\Command;
use Throwable;

class ImportProductManifestCommand extends Command
{
    protected $signature = 'catalog:import-manifest
        {manifest : Path to the reviewed FINAL-product-import-review.csv}
        {--dry-run : Validate and report only (the default)}
        {--execute : Actually write categories, subcategories, products and images}
        {--expect-stock= : Required. The approved stock quantity; every eligible manifest row must carry exactly this stock or it is rejected}
        {--allow-shared-supplier-code=* : Supplier code approved to cover several distinct products with different SKUs (repeatable)}
        {--no-resize : With --execute, copy original images instead of resizing them (not recommended: originals are 1.5-4 MB)}
        {--report= : Write the plan as JSON (and a .md summary next to it) to this path}';

    protected $description = 'Import NEW rows from the reviewed product manifest. Dry run unless --execute is given.';

    public function handle(ProductManifestImporter $importer): int
    {
        $execute = (bool) $this->option('execute');

        if ($execute && $this->option('dry-run')) {
            $this->error('Use either --dry-run or --execute, not both.');

            return self::FAILURE;
        }

        $expectedStock = $this->option('expect-stock');
        if ($expectedStock === null || ! preg_match('/^\d+$/', (string) $expectedStock)) {
            $this->error('--expect-stock=<whole number> is required (the approved stock quantity). Nothing was done.');

            return self::FAILURE;
        }
        $expectedStock = (int) $expectedStock;

        $this->line($execute ? 'Mode: EXECUTE' : 'Mode: DRY RUN (no database writes, no image copies)');
        $this->line('Database: '.config('database.default').' / '.config('database.connections.'.config('database.default').'.database'));

        $plan = $importer->plan((string) $this->argument('manifest'), $expectedStock, (array) $this->option('allow-shared-supplier-code'));
        $this->printPlan($plan);

        if ($report = $this->option('report')) {
            $this->writeReport($plan, (string) $report, $execute);
            $this->line("Report written to {$report}");
        }

        if ($plan['header_errors'] !== []) {
            return self::FAILURE;
        }

        if (! $execute) {
            $this->info('Dry run complete. Nothing was written.');

            return self::SUCCESS;
        }

        try {
            $result = $importer->execute($plan, ! $this->option('no-resize'));
        } catch (Throwable $exception) {
            $this->error('Import failed and was rolled back: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->table(['Created / skipped', 'Count'], collect($result)->map(fn ($count, $key) => [$key, $count])->values()->all());
        $this->info('Import complete. New products are inactive until reviewed.');

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private function printPlan(array $plan): void
    {
        foreach ($plan['header_errors'] as $error) {
            $this->error($error);
        }

        $this->table(['Measure', 'Count'], [
            ['Manifest rows', $plan['total_rows']],
            ['Eligible (status NEW)', $plan['eligible']],
            ['Valid, would be created', count($plan['valid'])],
            ['Invalid', count($plan['invalid'])],
            ['Existing SKU conflicts (skipped)', count($plan['existing_sku_conflicts'])],
            ['Supplier-code conflicts (skipped)', count($plan['supplier_code_conflicts'])],
            ['Missing images', count($plan['missing_images'])],
            ['Unresolved required fields', count($plan['unresolved_fields'])],
            ['Categories to create', count($plan['categories_to_create'])],
            ['Subcategories to create', count($plan['subcategories_to_create'])],
            ['Images to copy', count($plan['images_to_copy'])],
            ['Images already present (reused)', count($plan['images_already_present'])],
            ['Confirmed stock per product', $plan['expected_stock'] ?? '-'],
            ['Valid rows with exactly that stock', count(array_filter($plan['valid'], fn ($p) => $p['stock'] === ($plan['expected_stock'] ?? null)))],
            ['Valid rows that would be inactive (is_active=false)', count(array_filter($plan['valid'], fn ($p) => $p['is_active'] === false))],
        ]);

        foreach ($plan['excluded_counts'] as $status => $count) {
            $this->line("Excluded {$status}: {$count}");
        }

        foreach (['categories_to_create' => 'Category to create', 'subcategories_to_create' => 'Subcategory to create'] as $key => $label) {
            foreach ($plan[$key] as $name) {
                $this->line("{$label}: {$name}");
            }
        }

        foreach (array_merge($plan['existing_sku_conflicts'], $plan['supplier_code_conflicts']) as $conflict) {
            $this->warn('Conflict: '.$conflict);
        }

        foreach ($plan['invalid'] as $invalid) {
            $this->warn("Invalid row {$invalid['row_no']} ({$invalid['sku']}): ".implode('; ', $invalid['errors']));
        }
    }

    /**
     * @param  array<string, mixed>  $plan
     */
    private function writeReport(array $plan, string $path, bool $execute): void
    {
        $directory = dirname($path);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($path, json_encode($plan, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $md = [];
        $md[] = '# Product manifest import report ('.($execute ? 'EXECUTE' : 'DRY RUN').')';
        $md[] = '';
        $md[] = 'Generated: '.now()->toDateTimeString();
        $md[] = 'Manifest: `'.$plan['manifest'].'` (sha256 '.$plan['manifest_sha256'].')';
        $md[] = 'Database: '.config('database.default');
        $md[] = '';
        $md[] = '| Measure | Count |';
        $md[] = '|---|---|';
        $md[] = '| Manifest rows | '.$plan['total_rows'].' |';
        $md[] = '| Eligible NEW rows | '.$plan['eligible'].' |';
        $md[] = '| Valid (would be created) | '.count($plan['valid']).' |';
        $md[] = '| Invalid | '.count($plan['invalid']).' |';
        $md[] = '| Existing SKU conflicts | '.count($plan['existing_sku_conflicts']).' |';
        $md[] = '| Supplier-code conflicts | '.count($plan['supplier_code_conflicts']).' |';
        $md[] = '| Missing images | '.count($plan['missing_images']).' |';
        $md[] = '| Unresolved required fields | '.count($plan['unresolved_fields']).' |';
        $md[] = '| Images that would be copied | '.count($plan['images_to_copy']).' |';
        $md[] = '| Images already present | '.count($plan['images_already_present']).' |';
        $md[] = '| Confirmed stock per product | '.$plan['expected_stock'].' |';
        $md[] = '| Valid rows with exactly that stock | '.count(array_filter($plan['valid'], fn ($p) => $p['stock'] === $plan['expected_stock'])).' |';
        $md[] = '| Valid rows imported inactive | '.count(array_filter($plan['valid'], fn ($p) => $p['is_active'] === false)).' |';
        $md[] = '';
        $sections = [
            'Excluded statuses' => array_map(fn ($status, $count) => "{$status}: {$count}", array_keys($plan['excluded_counts']), $plan['excluded_counts']),
            'Categories to create' => $plan['categories_to_create'],
            'Existing categories used' => $plan['categories_existing'],
            'Subcategories to create' => $plan['subcategories_to_create'],
            'Existing subcategories used' => $plan['subcategories_existing'],
            'Existing SKU conflicts' => $plan['existing_sku_conflicts'],
            'Supplier-code conflicts' => $plan['supplier_code_conflicts'],
            'Missing images' => $plan['missing_images'],
            'Unresolved required fields' => $plan['unresolved_fields'],
            'Invalid rows' => array_map(fn ($row) => "row {$row['row_no']} ({$row['sku']}): ".implode('; ', $row['errors']), $plan['invalid']),
        ];
        foreach ($sections as $title => $items) {
            $md[] = "## {$title} (".count($items).')';
            $md[] = '';
            foreach ($items === [] ? ['none'] : $items as $item) {
                $md[] = '- '.$item;
            }
            $md[] = '';
        }
        $md[] = '## Products that would be created ('.count($plan['valid']).')';
        $md[] = '';
        $md[] = '| Row | SKU | Supplier code | Name | Cost | Price | Stock | Active | Category / subcategory | Image |';
        $md[] = '|---|---|---|---|---|---|---|---|---|---|';
        foreach ($plan['valid'] as $p) {
            $md[] = "| {$p['row_no']} | {$p['sku']} | {$p['supplier_code']} | ".str_replace('|', '\\|', $p['name'])." | {$p['cost_price']} | {$p['price']} | {$p['stock']} | ".($p['is_active'] ? 'yes' : 'no')." | {$p['category']} / {$p['subcategory']} | {$p['image']} |";
        }
        $md[] = '';
        $md[] = '## Images that would be copied ('.count($plan['images_to_copy']).')';
        $md[] = '';
        foreach ($plan['images_to_copy'] as $image) {
            $md[] = "- `{$image['source']}` → `storage/app/public/{$image['destination']}`";
        }

        file_put_contents(preg_replace('/\.json$/i', '', $path).'.md', implode("\n", $md)."\n");
    }
}
