<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Men\'s Clothing',
                'slug' => 'mens-clothing',
                'description' => 'Premium men\'s fashion for every occasion',
                'image' => 'categories/mens.jpg',
            ],
            [
                'name' => 'Women\'s Clothing',
                'slug' => 'womens-clothing',
                'description' => 'Elegant and trendy women\'s wear',
                'image' => 'categories/womens.jpg',
            ],
            [
                'name' => 'Accessories',
                'slug' => 'accessories',
                'description' => 'Complete your look with our accessories',
                'image' => 'categories/accessories.jpg',
            ],
            [
                'name' => 'Footwear',
                'slug' => 'footwear',
                'description' => 'Step out in style with our footwear collection',
                'image' => 'categories/footwear.jpg',
            ],
            [
                'name' => 'Sportswear',
                'slug' => 'sportswear',
                'description' => 'Performance and style combined',
                'image' => 'categories/sportswear.jpg',
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}