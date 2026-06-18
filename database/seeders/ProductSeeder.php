<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [];
        $subcategories = Subcategory::all();
        
        $productNames = [
            'T-Shirts' => ['Classic Cotton', 'Premium', 'V-Neck', 'Graphic', 'Striped', 'Oversized'],
            'Jeans' => ['Slim Fit', 'Regular', 'Skinny', 'Bootcut', 'Distressed', 'Stretch'],
            'Dresses' => ['Maxi', 'Summer', 'Cocktail', 'Floral', 'Bodycon', 'Casual'],
            'Watches' => ['Luxury', 'Sport', 'Classic', 'Digital', 'Smart', 'Chronograph'],
            'Sneakers' => ['Running', 'Casual', 'High Top', 'Low Top', 'Retro', 'Athletic'],
        ];
        
        $images = [
            'product1.jpg', 'product2.jpg', 'product3.jpg', 'product4.jpg', 'product5.jpg'
        ];
        
        foreach ($subcategories as $subcategory) {
            $nameList = $productNames[$subcategory->name] ?? ['Premium', 'Classic', 'Deluxe', 'Essential', 'Signature'];
            
            for ($i = 1; $i <= 4; $i++) {
                $productName = $nameList[array_rand($nameList)] . ' ' . $subcategory->name;
                $price = rand(25, 200) + 0.99;
                $comparePrice = rand(30, 250) + 0.99;
                
                Product::create([
                    'subcategory_id' => $subcategory->id,
                    'name' => $productName,
                    'slug' => strtolower(str_replace(' ', '-', $productName)) . '-' . uniqid(),
                    'description' => "This premium {$subcategory->name} is crafted with high-quality materials. 
                                    Perfect for any occasion, offering both style and comfort. 
                                    Available in multiple sizes and colors.",
                    'price' => $price,
                    'compare_price' => $price < $comparePrice ? $comparePrice : null,
                    'stock' => rand(0, 100),
                    'sku' => strtoupper(substr($subcategory->name, 0, 3)) . rand(1000, 9999),
                    'image' => $images[array_rand($images)],
                    'gallery' => json_encode($images),
                    'attributes' => json_encode([
                        'sizes' => ['S', 'M', 'L', 'XL'],
                        'colors' => ['Black', 'White', 'Blue', 'Red'],
                        'material' => ['Cotton', 'Polyester', 'Wool'],
                    ]),
                    'is_active' => true,
                    'is_featured' => rand(0, 1),
                ]);
            }
        }
        
        // Add more products to reach 100+
        for ($i = 0; $i < 80; $i++) {
            $subcategory = $subcategories->random();
            Product::create([
                'subcategory_id' => $subcategory->id,
                'name' => 'Product ' . ($i + 1) . ' - ' . $subcategory->name,
                'slug' => 'product-' . ($i + 1) . '-' . uniqid(),
                'description' => 'High quality product with excellent craftsmanship.',
                'price' => rand(15, 300) + 0.99,
                'stock' => rand(0, 150),
                'sku' => 'PRD' . rand(10000, 99999),
                'image' => $images[array_rand($images)],
                'is_active' => true,
            ]);
        }
    }
}