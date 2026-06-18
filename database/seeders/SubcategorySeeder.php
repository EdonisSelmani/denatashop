<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;

class SubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $subcategories = [
            'Men\'s Clothing' => ['T-Shirts', 'Jeans', 'Jackets', 'Suits', 'Shorts', 'Hoodies'],
            'Women\'s Clothing' => ['Dresses', 'Blouses', 'Skirts', 'Pants', 'Sweaters', 'Activewear'],
            'Accessories' => ['Watches', 'Bags', 'Jewelry', 'Sunglasses', 'Hats', 'Belts'],
            'Footwear' => ['Sneakers', 'Boots', 'Sandals', 'Loafers', 'Running Shoes', 'Formal Shoes'],
            'Sportswear' => ['Gym Wear', 'Yoga Pants', 'Sports Bras', 'Training Shoes', 'Compression Gear'],
        ];

        foreach ($subcategories as $categoryName => $subs) {
            $category = Category::where('name', $categoryName)->first();
            if ($category) {
                foreach ($subs as $sub) {
                    Subcategory::create([
                        'category_id' => $category->id,
                        'name' => $sub,
                        'slug' => strtolower(str_replace(' ', '-', $sub)),
                        'description' => "Premium {$sub} collection",
                    ]);
                }
            }
        }
    }
}