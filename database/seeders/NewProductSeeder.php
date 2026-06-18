<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class NewProductSeeder extends Seeder
{
    private $images = [
        'product1.jpg', 'product2.jpg', 'product3.jpg', 'product4.jpg', 'product5.jpg',
    ];
    
    private $brands = ['Bosch', 'Makita', 'DeWalt', 'Stanley', 'Einhell', 'Black+Decker', 'Metabo'];
    private $materials = ['Çelik', 'Alumini', 'Plastik', 'Gize', 'Bronz', 'Bakër'];
    private $origins = ['Gjermani', 'Itali', 'Kinë', 'Turqi', 'Poloni'];
    
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Fshi produktet ekzistuese pa truncate
        Product::query()->delete();
        
        $subcategories = Subcategory::with('category')->get();
        $counter = 1;
        
        foreach ($subcategories as $subcategory) {
            $this->generateProductsForSubcategory($subcategory, $counter);
        }
        
        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('Products seeded successfully! Total: ' . Product::count());
    }
    
    private function generateProductsForSubcategory($subcategory, &$counter)
    {
        $productsPerSubcategory = 20;
        
        for ($i = 1; $i <= $productsPerSubcategory; $i++) {
            $basePrice = $this->getBasePriceByCategory($subcategory->category->name);
            $price = $basePrice + rand(0, 50) + (rand(0, 99) / 100);
            $comparePrice = rand(0, 2) === 0 ? $price + rand(10, 100) : null;
            $stock = rand(0, 150);
            
            $productName = $this->generateProductName($subcategory->name, $i);
            $sku = $this->generateSku($subcategory->category->name, $subcategory->name, $i);
            $brand = $this->brands[array_rand($this->brands)];
            $material = $this->materials[array_rand($this->materials)];
            $origin = $this->origins[array_rand($this->origins)];
            
            try {
                Product::create([
                    'subcategory_id' => $subcategory->id,
                    'name' => $productName,
                    'slug' => Str::slug($productName) . '-' . $counter . uniqid(),
                    'description' => $this->generateDescription($brand, $material, $origin, $subcategory->name),
                    'price' => $price,
                    'compare_price' => $comparePrice,
                    'stock' => $stock,
                    'sku' => $sku,
                    'image' => $this->images[array_rand($this->images)],
                    'gallery' => json_encode($this->images),
                    'attributes' => json_encode($this->generateAttributes($subcategory->name)),
                    'is_active' => true,
                    'is_featured' => ($i <= 5) ? true : false,
                ]);
                $counter++;
            } catch (\Exception $e) {
                $this->command->error("Error creating product: " . $e->getMessage());
            }
        }
    }
    
    private function getBasePriceByCategory($categoryName): float
    {
        return match($categoryName) {
            'Tusha' => 25.00,
            'Vegla Pune' => 15.00,
            'Vegla Kopshti' => 12.00,
            'Elektronike' => 50.00,
            'Ujësjellës' => 8.00,
            default => 20.00,
        };
    }
    
    private function generateProductName($subcategoryName, $index): string
    {
        $adjectives = ['Pro', 'Premium', 'Deluxe', 'Professional', 'Heavy Duty', 'Classic'];
        $adjective = $adjectives[array_rand($adjectives)];
        return "$adjective $subcategoryName - Modeli $index";
    }
    
    private function generateDescription($brand, $material, $origin, $subcategory): string
    {
        return "**{$brand}** {$subcategory} i cilësisë së lartë.\n\n" .
               "**Karakteristikat kryesore:**\n" .
               "• Material: {$material} i qëndrueshëm\n" .
               "• Origjina: {$origin}\n" .
               "• Projektim ergonomik për përdorim të lehtë\n" .
               "• Rezistent ndaj konsumit dhe gërryerjes\n" .
               "• I sertifikuar sipas standardeve evropiane\n\n" .
               "**Garancia:** 24 muaj garanci nga prodhuesi.";
    }
    
    private function generateSku($categoryName, $subcategoryName, $index): string
    {
        $catCode = strtoupper(substr($categoryName, 0, 3));
        $subCode = strtoupper(substr($subcategoryName, 0, 3));
        $random = strtoupper(substr(uniqid(), -4));
        return "{$catCode}-{$subCode}-{$random}-{$index}";
    }
    
    private function generateAttributes($subcategoryName): array
    {
        $attributes = [];
        
        if (in_array($subcategoryName, ['Gypa', 'Lidhesa metali', 'Lidhesa plastike', 'Lidhesa te bardha'])) {
            $attributes['sizes'] = ['1/2"', '3/4"', '1"', '1.5"', '2"'];
        } elseif (in_array($subcategoryName, ['Diska', 'Sharra', 'Llampa'])) {
            $attributes['sizes'] = ['115mm', '125mm', '150mm', '180mm', '230mm'];
        } else {
            $attributes['sizes'] = ['S', 'M', 'L', 'XL'];
        }
        
        $attributes['colors'] = ['E Zezë', 'E Kuqe', 'E Kaltër', 'E Gjelbër'];
        
        if (in_array($subcategoryName, ['Burmashina', 'Brusalica', 'Motorra te druve'])) {
            $attributes['power'] = ['500W', '800W', '1200W', '1500W'];
        }
        
        return $attributes;
    }
}