<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewCategorySeeder extends Seeder
{
    public function run(): void
    {
        // Fshi të gjitha kategoritë ekzistuese (produktet do të fshihen automatikisht)
        Category::truncate();
        
        $categories = [
            [
                'name' => 'Tusha',
                'description' => 'Pajisje dhe materiale cilësore për instalime hidraulike dhe banjo',
                'image' => 'categories/tusha.jpg',
                'order' => 1,
            ],
            [
                'name' => 'Vegla Pune',
                'description' => 'Vegla profesionale dhe amatore për punëtori dhe ndërtim',
                'image' => 'categories/vegla-pune.jpg',
                'order' => 2,
            ],
            [
                'name' => 'Vegla Kopshti',
                'description' => 'Pajisje dhe vegla për mirëmbajtjen e kopshtit dhe oborrit',
                'image' => 'categories/vegla-kopshti.jpg',
                'order' => 3,
            ],
            [
                'name' => 'Elektronike',
                'description' => 'Pajisje elektronike dhe makina për punime të ndryshme',
                'image' => 'categories/elektronike.jpg',
                'order' => 4,
            ],
            [
                'name' => 'Ujësjellës',
                'description' => 'Materiale dhe lidhës për instalime ujësjellësi',
                'image' => 'categories/ujesjelles.jpg',
                'order' => 5,
            ],
        ];
        
        foreach ($categories as $categoryData) {
            Category::create([
                'name' => $categoryData['name'],
                'slug' => Str::slug($categoryData['name']),
                'description' => $categoryData['description'],
                'image' => $categoryData['image'],
                'is_active' => true,
            ]);
        }
    }
}