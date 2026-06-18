<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ImportProductsCommand extends Command
{
    protected $signature = 'products:import 
                            {file? : Emri i CSV file (default: products.csv)} 
                            {--dry-run : Testo pa insertuar në database}';
    
    protected $description = 'Import products from CSV file';

    public function handle()
    {
        $fileName = $this->argument('file') ?? 'products.csv';
        $path = storage_path('app/import/' . $fileName);
        $dryRun = $this->option('dry-run');
        
        // Kontrollo nëse file ekziston
        if (!file_exists($path)) {
            $this->error("❌ File not found: {$path}");
            $this->info("\n📁 Please create the CSV file at:");
            $this->info("   storage/app/import/products.csv");
            return 1;
        }
        
        if ($dryRun) {
            $this->warn("\n⚠️  DRY RUN MODE - No data will be inserted\n");
        }
        
        // Lexo CSV
        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);
        
        // Verifiko header-et
        $requiredHeaders = ['category', 'subcategory', 'name', 'price', 'stock'];
        $missingHeaders = array_diff($requiredHeaders, $header);
        
        if (!empty($missingHeaders)) {
            $this->error("❌ Missing required columns: " . implode(', ', $missingHeaders));
            return 1;
        }
        
        $this->info("📋 CSV Headers: " . implode(' | ', $header));
        $this->info("==================================================");
        
        $successCount = 0;
        $errorCount = 0;
        
        DB::beginTransaction();
        
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);
                
                try {
                    $this->createProduct($data, $dryRun);
                    $successCount++;
                    $this->info("✅ Imported: " . ($data['name'] ?? 'Unknown'));
                } catch (\Exception $e) {
                    $errorCount++;
                    $this->error("❌ Error for '{$data['name']}': " . $e->getMessage());
                    
                    if (!$dryRun) {
                        throw new \Exception("Stopped due to error: " . $e->getMessage());
                    }
                }
            }
            
            if (!$dryRun) {
                DB::commit();
                $this->info("\n✅ Committed to database!");
            } else {
                DB::rollBack();
                $this->info("\n⚠️  Dry run - No changes made to database");
            }
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("\n❌ Transaction rolled back: " . $e->getMessage());
            return 1;
        }
        
        fclose($handle);
        
        // Summary
        $this->info("\n==================================================");
        $this->info("📊 IMPORT SUMMARY");
        $this->info("==================================================");
        $this->info("✅ Successful: {$successCount}");
        $this->info("❌ Errors: {$errorCount}");
        
        return 0;
    }
    
    private function createProduct($data, $dryRun = false)
    {
        // Validimi
        if (empty($data['category'])) {
            throw new \Exception("Category is required");
        }
        if (empty($data['subcategory'])) {
            throw new \Exception("Subcategory is required");
        }
        if (empty($data['name'])) {
            throw new \Exception("Product name is required");
        }
        if (empty($data['price']) || !is_numeric($data['price'])) {
            throw new \Exception("Valid price is required");
        }
        
        // Gjej ose krijo kategorinë
        $category = Category::firstOrCreate(
            ['slug' => Str::slug($data['category'])],
            [
                'name' => $data['category'],
                'slug' => Str::slug($data['category']),
                'description' => $data['category_description'] ?? "Produkte {$data['category']}",
                'is_active' => true,
            ]
        );
        
        // Gjej ose krijo subkategorinë
        $subcategory = Subcategory::firstOrCreate(
            [
                'slug' => Str::slug($data['subcategory']), 
                'category_id' => $category->id
            ],
            [
                'category_id' => $category->id,
                'name' => $data['subcategory'],
                'slug' => Str::slug($data['subcategory']),
                'description' => $data['subcategory_description'] ?? "Produkte {$data['subcategory']}",
                'is_active' => true,
            ]
        );
        
        if ($dryRun) {
            return true;
        }
        
        // Krijo produktin
        return Product::create([
            'subcategory_id' => $subcategory->id,
            'name' => $data['name'],
            'slug' => Str::slug($data['name']) . '-' . uniqid(),
            'description' => $data['description'] ?? $this->generateDescription($data),
            'price' => floatval($data['price']),
            'compare_price' => isset($data['compare_price']) && $data['compare_price'] ? floatval($data['compare_price']) : null,
            'stock' => intval($data['stock'] ?? 10),
            'sku' => $data['sku'] ?? $this->generateSku($data),
            'image' => $data['image'] ?? null,
            'is_active' => isset($data['is_active']) ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
            'is_featured' => isset($data['is_featured']) ? filter_var($data['is_featured'], FILTER_VALIDATE_BOOLEAN) : false,
        ]);
    }
    
    private function generateDescription($data): string
    {
        $brands = ['Bosch', 'Makita', 'DeWalt', 'Stanley', 'Einhell'];
        $brand = $brands[array_rand($brands)];
        
        return "**{$brand}** {$data['name']} - Produkt cilësor për {$data['subcategory']}.\n\n" .
               "**Karakteristikat kryesore:**\n" .
               "• Material premium\n" .
               "• Dizajn ergonomik\n" .
               "• Rezistent ndaj konsumit\n" .
               "• 24 muaj garanci";
    }
    
    private function generateSku($data): string
    {
        $catCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['category']), 0, 3));
        $subCode = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $data['subcategory']), 0, 3));
        $random = strtoupper(Str::random(4));
        
        return "{$catCode}-{$subCode}-{$random}";
    }
}