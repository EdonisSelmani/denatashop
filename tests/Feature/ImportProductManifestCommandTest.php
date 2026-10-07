<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use App\Services\ProductManifestImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportProductManifestCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $fixtureDirectory;

    private const HEADERS = [
        'row_no', 'status', 'review_reasons', 'supplier_code', 'proposed_sku', 'supplier_card_name', 'proposed_display_name',
        'cost_price', 'proposed_selling_price', 'stock', 'top_level_category', 'proposed_subcategory', 'category_confidence',
        'usb_folder_suggestion', 'source_product_image', 'source_image_sha1', 'multi_code_image', 'source_supplier_card',
        'other_supplier_cards', 'name_confidence', 'price_confidence', 'existing_sku', 'existing_name', 'existing_price',
        'existing_match_basis', 'previous_catalog_sku', 'previous_catalog_price', 'notes',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->fixtureDirectory = storage_path('framework/testing/manifest-import');
        File::deleteDirectory($this->fixtureDirectory);
        File::ensureDirectoryExists($this->fixtureDirectory.'/Produktet VS/ujesjelles/EK VALVOLA VS');
        File::ensureDirectoryExists($this->fixtureDirectory.'/Produktet VS/Elektrik VS');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->fixtureDirectory);

        parent::tearDown();
    }

    public function test_dry_run_is_the_default_and_makes_no_database_or_storage_changes(): void
    {
        $this->createExistingTaxonomy();
        $manifest = $this->manifest([
            $this->row(['proposed_sku' => 'VS-VAL-10081', 'supplier_code' => '10081']),
            $this->row(['row_no' => 2, 'proposed_sku' => 'VS-ELE-1548', 'supplier_code' => '1548', 'top_level_category' => 'Elektronike', 'proposed_subcategory' => 'Shtekera', 'image' => 'Elektrik VS/1548.jpg']),
        ]);

        $before = [Category::count(), Subcategory::count(), Product::count()];

        $this->artisan('catalog:import-manifest', ['manifest' => $manifest, '--expect-stock' => 10])
            ->expectsOutputToContain('Mode: DRY RUN')
            ->expectsOutputToContain('Dry run complete. Nothing was written.')
            ->assertSuccessful();

        $this->assertSame($before, [Category::count(), Subcategory::count(), Product::count()]);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_command_refuses_to_run_without_confirmed_stock(): void
    {
        $manifest = $this->manifest([$this->row()]);

        $this->artisan('catalog:import-manifest', ['manifest' => $manifest])->assertFailed();
        $this->artisan('catalog:import-manifest', ['manifest' => $manifest, '--execute' => true, '--no-resize' => true])->assertFailed();
        $this->artisan('catalog:import-manifest', ['manifest' => $manifest, '--execute' => true, '--no-resize' => true, '--expect-stock' => 'ten'])->assertFailed();

        $this->assertDatabaseCount('products', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_every_imported_product_gets_exactly_the_confirmed_stock_and_stays_inactive(): void
    {
        $manifest = $this->manifest([
            $this->row(['row_no' => 1, 'proposed_sku' => 'VS-VAL-1', 'supplier_code' => '1001']),
            $this->row(['row_no' => 2, 'proposed_sku' => 'VS-VAL-2', 'supplier_code' => '1002']),
            $this->row(['row_no' => 3, 'proposed_sku' => 'VS-VAL-3', 'supplier_code' => '1003']),
        ]);

        $plan = $this->plan($manifest);
        $this->assertSame([10, 10, 10], array_column($plan['valid'], 'stock'));
        $this->assertSame([false, false, false], array_column($plan['valid'], 'is_active'));

        $this->executeImport($manifest);

        $this->assertSame([10], Product::pluck('stock')->unique()->values()->all());
        $this->assertSame(0, Product::where('is_active', true)->count());
        $this->assertSame(3, Product::count());
    }

    public function test_only_new_rows_are_eligible(): void
    {
        $manifest = $this->manifest([
            $this->row(['row_no' => 1, 'proposed_sku' => 'VS-VAL-1', 'supplier_code' => '1001']),
            $this->row(['row_no' => 2, 'status' => 'MANUAL_REVIEW', 'proposed_sku' => 'VS-VAL-2', 'supplier_code' => '1002']),
            $this->row(['row_no' => 3, 'status' => 'EXISTING_LOCAL', 'proposed_sku' => 'VS-VAL-3', 'supplier_code' => '1003']),
            $this->row(['row_no' => 4, 'status' => 'EXISTING_PREVIOUS_CATALOG', 'proposed_sku' => 'VS-VAL-4', 'supplier_code' => '1004']),
            $this->row(['row_no' => 5, 'status' => 'DUPLICATE_SOURCE', 'proposed_sku' => 'VS-VAL-5', 'supplier_code' => '1005']),
        ]);

        $plan = $this->plan($manifest);
        $this->assertSame(1, $plan['eligible']);
        $this->assertSame(['VS-VAL-1'], array_column($plan['valid'], 'sku'));

        $this->executeImport($manifest);

        $this->assertSame(['VS-VAL-1'], Product::pluck('sku')->all());
    }

    public function test_existing_sku_is_skipped_and_existing_product_untouched(): void
    {
        [, $subcategory] = $this->createExistingTaxonomy();
        $existing = Product::create([
            'subcategory_id' => $subcategory->id, 'name' => 'Old name', 'slug' => 'old-name', 'description' => 'Kept',
            'price' => '9.99', 'stock' => 4, 'sku' => 'VS-VAL-10081', 'is_active' => true,
        ]);

        $manifest = $this->manifest([$this->row(['proposed_sku' => 'vs-val-10081', 'supplier_code' => '99999'])]);
        $plan = $this->plan($manifest);

        $this->assertCount(0, $plan['valid']);
        $this->assertCount(1, $plan['existing_sku_conflicts']);

        $this->executeImport($manifest);

        $existing->refresh();
        $this->assertSame('Old name', $existing->name);
        $this->assertSame('9.99', $existing->price);
        $this->assertSame(4, $existing->stock);
        $this->assertDatabaseCount('products', 1);
    }

    public function test_existing_supplier_code_in_vs_sku_is_a_conflict(): void
    {
        [, $subcategory] = $this->createExistingTaxonomy();
        Product::create([
            'subcategory_id' => $subcategory->id, 'name' => 'Same code', 'slug' => 'same-code', 'description' => 'x',
            'price' => '1.00', 'stock' => 1, 'sku' => 'VS-MJE-10081',
        ]);

        $plan = $this->plan($this->manifest([$this->row()]));

        $this->assertCount(0, $plan['valid']);
        $this->assertCount(1, $plan['supplier_code_conflicts']);
    }

    public function test_invalid_price_is_rejected(): void
    {
        $manifest = $this->manifest([
            $this->row(['row_no' => 1, 'proposed_sku' => 'VS-A-1', 'supplier_code' => '1', 'cost_price' => '1,50', 'proposed_selling_price' => '2.20']),
            $this->row(['row_no' => 2, 'proposed_sku' => 'VS-A-2', 'supplier_code' => '2', 'cost_price' => '1.50', 'proposed_selling_price' => '2.17']),
            $this->row(['row_no' => 3, 'proposed_sku' => 'VS-A-3', 'supplier_code' => '3', 'cost_price' => 'UNRESOLVED', 'proposed_selling_price' => 'UNRESOLVED']),
        ]);

        $plan = $this->plan($manifest);

        $this->assertCount(0, $plan['valid']);
        $this->assertCount(3, $plan['invalid']);
        $this->assertStringContainsString('not a valid 2-decimal amount', implode(' ', $plan['invalid'][0]['errors']));
        $this->assertStringContainsString('does not equal cost 1.50 x 1.45 (expected 2.20)', implode(' ', $plan['invalid'][1]['errors']));
        $this->assertStringContainsString('cost_price is unresolved', implode(' ', $plan['invalid'][2]['errors']));
    }

    public function test_missing_image_is_rejected(): void
    {
        $manifest = $this->manifest([$this->row(['image' => 'ujesjelles/EK VALVOLA VS/does-not-exist.jpg', 'create_image' => false])]);

        $plan = $this->plan($manifest);

        $this->assertCount(0, $plan['valid']);
        $this->assertCount(1, $plan['missing_images']);
        $this->assertContains('source image not found', $plan['invalid'][0]['errors']);
    }

    public function test_duplicate_sku_in_manifest_rejects_both_rows(): void
    {
        $manifest = $this->manifest([
            $this->row(['row_no' => 1, 'proposed_sku' => 'VS-VAL-1', 'supplier_code' => '1001']),
            $this->row(['row_no' => 2, 'proposed_sku' => 'vs-val-1', 'supplier_code' => '1002']),
        ]);

        $plan = $this->plan($manifest);

        $this->assertCount(0, $plan['valid']);
        $this->assertCount(2, $plan['invalid']);
        $this->assertContains('proposed_sku is duplicated in the manifest', $plan['invalid'][0]['errors']);
    }

    public function test_shared_supplier_code_requires_explicit_approval(): void
    {
        $manifest = $this->manifest([
            $this->row(['row_no' => 1, 'proposed_sku' => 'VS-MJE-1434-A', 'supplier_code' => '1434', 'image' => 'VEGLA PER NGJYRE VS/1434.jpg']),
            $this->row(['row_no' => 2, 'proposed_sku' => 'VS-MJE-1434-B', 'supplier_code' => '1434', 'image' => 'VEGLA PER NGJYRE VS/1434..jpg']),
        ]);

        $withoutApproval = $this->plan($manifest);
        $this->assertCount(0, $withoutApproval['valid']);
        $this->assertContains('supplier_code is duplicated among eligible rows', $withoutApproval['invalid'][0]['errors']);

        $withApproval = $this->plan($manifest, ['1434']);
        $this->assertSame(['VS-MJE-1434-A', 'VS-MJE-1434-B'], array_column($withApproval['valid'], 'sku'));
        $this->assertCount(2, array_unique(array_column($withApproval['valid'], 'image')));
    }

    public function test_rows_with_any_other_stock_are_rejected(): void
    {
        $manifest = $this->manifest([
            $this->row(['row_no' => 1, 'proposed_sku' => 'VS-A-1', 'supplier_code' => '1', 'stock' => '5']),
            $this->row(['row_no' => 2, 'proposed_sku' => 'VS-A-2', 'supplier_code' => '2', 'stock' => 'UNRESOLVED']),
            $this->row(['row_no' => 3, 'proposed_sku' => 'VS-A-3', 'supplier_code' => '3', 'stock' => '10.5']),
            $this->row(['row_no' => 4, 'proposed_sku' => 'VS-A-4', 'supplier_code' => '4', 'stock' => '']),
            $this->row(['row_no' => 5, 'proposed_sku' => 'VS-A-5', 'supplier_code' => '5', 'stock' => '10']),
        ]);

        $plan = $this->plan($manifest);
        $this->assertSame(['VS-A-5'], array_column($plan['valid'], 'sku'));
        $this->assertCount(4, $plan['invalid']);
        $this->assertContains('stock 5 does not equal the confirmed stock 10', $plan['invalid'][0]['errors']);

        // A manifest carrying 10 does not import when a different quantity is confirmed.
        $this->assertCount(0, $this->plan($this->manifest([$this->row()]), [], 5)['valid']);

        $this->executeImport($manifest);
        $this->assertSame(['VS-A-5'], Product::pluck('sku')->all());
    }

    public function test_execute_refuses_a_plan_whose_stock_was_altered(): void
    {
        $plan = $this->plan($this->manifest([$this->row()]));
        $plan['valid'][0]['stock'] = 0;

        $this->expectException(\RuntimeException::class);
        try {
            app(ProductManifestImporter::class)->execute($plan, false);
        } finally {
            $this->assertDatabaseCount('products', 0);
            $this->assertSame([], Storage::disk('public')->allFiles());
        }
    }

    public function test_dry_run_reports_categories_to_create_without_creating_them(): void
    {
        $this->createExistingTaxonomy();
        $manifest = $this->manifest([
            $this->row(['row_no' => 1, 'proposed_sku' => 'VS-VAL-1', 'supplier_code' => '1001']),
            $this->row(['row_no' => 2, 'proposed_sku' => 'VS-ELE-2', 'supplier_code' => '1002', 'top_level_category' => 'Elektronike', 'proposed_subcategory' => 'Shtekera']),
        ]);

        $plan = $this->plan($manifest);

        $this->assertSame(['Ujësjellës'], $plan['categories_to_create']);
        $this->assertSame(['Ujësjellës / Valvola'], $plan['subcategories_to_create']);
        $this->assertSame(['Elektronike'], $plan['categories_existing']);
        $this->assertSame(['Elektronike / Shtekera'], $plan['subcategories_existing']);
        $this->assertDatabaseMissing('categories', ['slug' => 'ujesjelles']);
        $this->assertDatabaseMissing('subcategories', ['slug' => 'valvola']);
    }

    public function test_execute_creates_missing_taxonomy_and_reuses_existing(): void
    {
        [$elektronike, $shtekera] = $this->createExistingTaxonomy();
        $manifest = $this->manifest([
            $this->row(['row_no' => 1, 'proposed_sku' => 'VS-VAL-1', 'supplier_code' => '1001']),
            $this->row(['row_no' => 2, 'proposed_sku' => 'VS-ELE-2', 'supplier_code' => '1002', 'top_level_category' => 'Elektronike', 'proposed_subcategory' => 'Shtekera']),
        ]);

        $this->executeImport($manifest);

        $this->assertSame(2, Category::count());
        $valvola = Subcategory::where('slug', 'valvola')->firstOrFail();
        $this->assertSame('Valvola', $valvola->name);
        $this->assertSame('Ujësjellës', $valvola->category->name);
        $this->assertSame($shtekera->id, Product::where('sku', 'VS-ELE-2')->value('subcategory_id'));
        $this->assertSame($elektronike->id, $shtekera->fresh()->category_id);
    }

    public function test_subcategory_existing_under_another_category_is_rejected(): void
    {
        $this->createExistingTaxonomy();
        $plan = $this->plan($this->manifest([
            $this->row(['top_level_category' => 'Ujësjellës', 'proposed_subcategory' => 'Shtekera']),
        ]));

        $this->assertCount(0, $plan['valid']);
        $this->assertStringContainsString('already exists under a different category', implode(' ', $plan['invalid'][0]['errors']));
    }

    public function test_running_execute_twice_does_not_duplicate_products(): void
    {
        $manifest = $this->manifest([
            $this->row(['row_no' => 1, 'proposed_sku' => 'VS-VAL-1', 'supplier_code' => '1001']),
            $this->row(['row_no' => 2, 'proposed_sku' => 'VS-VAL-2', 'supplier_code' => '1002', 'image' => 'ujesjelles/EK VALVOLA VS/1002.jpg']),
        ]);

        $this->executeImport($manifest);
        $this->executeImport($manifest);

        $this->assertDatabaseCount('products', 2);
        $this->assertDatabaseCount('categories', 1);
        $this->assertDatabaseCount('subcategories', 1);

        $secondPlan = $this->plan($manifest);
        $this->assertCount(0, $secondPlan['valid']);
        $this->assertCount(2, $secondPlan['existing_sku_conflicts']);
    }

    public function test_prices_and_metadata_are_preserved_exactly_and_product_is_inactive(): void
    {
        $manifest = $this->manifest([
            $this->row(['proposed_sku' => 'VS-UJE-21553', 'supplier_code' => '21553', 'cost_price' => '3.90', 'proposed_selling_price' => '5.65', 'notes' => 'Cards disagree on price: older=3.50']),
        ]);

        $this->executeImport($manifest);

        $product = Product::where('sku', 'VS-UJE-21553')->firstOrFail();
        $this->assertSame('5.65', $product->price);
        $this->assertEquals(5.65, (float) $product->getRawOriginal('price'));
        $this->assertSame('3.90', $product->getAttribute('attributes')['cost_price']);
        $this->assertSame('21553', $product->getAttribute('attributes')['supplier_code']);
        $this->assertSame(10, $product->stock);
        $this->assertArrayNotHasKey('stock_status', $product->getAttribute('attributes'));
        $this->assertSame('Cards disagree on price: older=3.50', $product->getAttribute('attributes')['import']['notes']);
        $this->assertFalse($product->is_active);
        $this->assertNull($product->compare_price);
        Storage::disk('public')->assertExists($product->image);
    }

    public function test_future_imports_apply_the_minimum_selling_price_without_changing_cost_price(): void
    {
        $manifest = $this->manifest([
            $this->row([
                'proposed_sku' => 'VS-MIN-1',
                'supplier_code' => 'MIN-1',
                'cost_price' => '0.20',
                'proposed_selling_price' => '1.50',
            ]),
        ]);

        $this->executeImport($manifest);

        $product = Product::where('sku', 'VS-MIN-1')->firstOrFail();
        $this->assertSame('1.50', $product->price);
        $this->assertSame('0.20', $product->getAttribute('attributes')['cost_price']);
    }

    public function test_multi_code_rows_share_one_copied_image(): void
    {
        $manifest = $this->manifest([
            $this->row(['row_no' => 1, 'proposed_sku' => 'VS-ELE-1871', 'supplier_code' => '1871', 'image' => 'Elektrik VS/1871,-1872.jpg']),
            $this->row(['row_no' => 2, 'proposed_sku' => 'VS-ELE-1872', 'supplier_code' => '1872', 'image' => 'Elektrik VS/1871,-1872.jpg']),
        ]);

        $plan = $this->plan($manifest);
        $this->assertCount(1, $plan['images_to_copy']);

        $this->executeImport($manifest);

        $images = Product::pluck('image')->unique()->values()->all();
        $this->assertCount(1, $images);
        $this->assertCount(1, Storage::disk('public')->allFiles());
    }

    public function test_failed_execute_rolls_back_database_and_copied_images(): void
    {
        $manifest = $this->manifest([$this->row()]);
        $plan = $this->plan($manifest);
        // Corrupt the plan after validation to force a failure inside the transaction.
        $plan['valid'][] = array_merge($plan['valid'][0], ['sku' => 'VS-VAL-OTHER', 'subcategory' => '', 'category' => '']);

        try {
            app(ProductManifestImporter::class)->execute($plan, false);
            $this->fail('Expected the import to fail.');
        } catch (\Throwable) {
            // expected
        }

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('categories', 0);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_destination_path_follows_existing_product_image_layout(): void
    {
        $importer = app(ProductManifestImporter::class);

        $this->assertSame('products/vs/ujesjelles-EK-VALVOLA-VS/10081.jpg', $importer->destinationPath('D:\\Produktet VS\\ujesjelles\\EK VALVOLA VS\\10081.jpg'));
        $this->assertNotSame(
            $importer->destinationPath('D:\\Produktet VS\\VEGLA PER NGJYRE VS\\1434.jpg'),
            $importer->destinationPath('D:\\Produktet VS\\VEGLA PER NGJYRE VS\\1434..jpg'),
        );
    }

    /**
     * @param  array<int, string>  $sharedCodes
     * @return array<string, mixed>
     */
    private function plan(string $manifest, array $sharedCodes = [], int $expectedStock = 10): array
    {
        return app(ProductManifestImporter::class)->plan($manifest, $expectedStock, $sharedCodes);
    }

    private function executeImport(string $manifest): void
    {
        $this->artisan('catalog:import-manifest', [
            'manifest' => $manifest,
            '--execute' => true,
            '--expect-stock' => 10,
            '--no-resize' => true,
        ])->assertSuccessful();
    }

    /**
     * @return array{0: Category, 1: Subcategory}
     */
    private function createExistingTaxonomy(): array
    {
        $category = Category::create(['name' => 'Elektronike', 'slug' => 'elektronike', 'is_active' => true]);
        $subcategory = Subcategory::create(['category_id' => $category->id, 'name' => 'Shtekera', 'slug' => 'shtekera', 'is_active' => true]);

        return [$category, $subcategory];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function row(array $overrides = []): array
    {
        $image = $overrides['image'] ?? 'ujesjelles/EK VALVOLA VS/10081.jpg';
        $createImage = $overrides['create_image'] ?? true;
        unset($overrides['image'], $overrides['create_image']);

        $path = $this->fixtureDirectory.'/Produktet VS/'.$image;
        if ($createImage && ! is_file($path)) {
            File::ensureDirectoryExists(dirname($path));
            File::put($path, 'fake-jpeg-'.$image);
        }

        return array_merge([
            'row_no' => 1, 'status' => 'NEW', 'review_reasons' => '', 'supplier_code' => '10081', 'proposed_sku' => 'VS-VAL-10081',
            'supplier_card_name' => 'VALVOL METALI SFERIKE 1/2"', 'proposed_display_name' => 'Valvol metali sferike 1/2"',
            'cost_price' => '1.50', 'proposed_selling_price' => '2.20', 'stock' => '10',
            'top_level_category' => 'Ujësjellës', 'proposed_subcategory' => 'Valvola (NEW)', 'category_confidence' => 'high',
            'usb_folder_suggestion' => '', 'source_product_image' => $path, 'source_image_sha1' => sha1($image), 'multi_code_image' => 'no',
            'source_supplier_card' => 'D:\\card.jpg', 'other_supplier_cards' => '', 'name_confidence' => 'high', 'price_confidence' => 'high',
            'existing_sku' => '', 'existing_name' => '', 'existing_price' => '', 'existing_match_basis' => '', 'previous_catalog_sku' => '',
            'previous_catalog_price' => '', 'notes' => '',
        ], $overrides);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function manifest(array $rows): string
    {
        $path = $this->fixtureDirectory.'/manifest-'.uniqid().'.csv';
        $handle = fopen($path, 'w');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, self::HEADERS);
        foreach ($rows as $row) {
            fputcsv($handle, array_map(fn ($header) => $row[$header], self::HEADERS));
        }
        fclose($handle);

        return $path;
    }
}
