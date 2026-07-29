<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class DeployCatalogToProductionCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_dry_run_with_testing_override_does_not_write_catalog_rows(): void
    {
        $fixtureDirectory = storage_path('framework/testing/catalog-deploy');
        File::ensureDirectoryExists($fixtureDirectory);

        $catalogPath = $fixtureDirectory.'/catalog.csv';
        $bateriaPath = $fixtureDirectory.'/bateria.csv';

        File::put($catalogPath, implode("\n", [
            'category,subcategory,name,description,price,compare_price,stock,sku,image,is_active,is_featured',
            'Bateria,"Rubineta kuzhine","Rubinet test","Dry-run product",45.00,,7,BATDRY001,products/test-bateria.jpg,1,0',
            'Vegla,"Kaqavida","Kaqavide test","Dry-run low price product",0.25,,3,VSDRY001,,1,0',
        ])."\n");

        File::put($bateriaPath, implode("\n", [
            'sku,name,subcategory,current_price,proposed_price,confidence,reason,image_path',
            'BATDRY001,"Rubinet test","Rubineta kuzhine",45.00,29.90,high,"Test override",products/test-bateria.jpg',
        ])."\n");

        $this->artisan('catalog:deploy-to-production', [
            '--dry-run' => true,
            '--allow-testing-override' => true,
            '--catalog' => $catalogPath,
            '--bateria-prices' => $bateriaPath,
        ])->assertSuccessful();

        $this->assertDatabaseCount('categories', 0);
        $this->assertDatabaseCount('subcategories', 0);
        $this->assertDatabaseCount('products', 0);
    }
}
