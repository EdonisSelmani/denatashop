<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            NewCategorySeeder::class,
            NewSubcategorySeeder::class,
            NewProductSeeder::class,
            AdminUserSeeder::class, // Nëse e keni
        ]);
    }
}