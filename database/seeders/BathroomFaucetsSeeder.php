<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BathroomFaucetsSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Gjej ose krijo kategorinë Tusha
        $category = Category::firstOrCreate(
            ['slug' => 'tusha'],
            [
                'name' => 'Tusha',
                'slug' => 'tusha',
                'description' => 'Pajisje hidraulike për banjo dhe kuzhinë',
                'is_active' => true,
            ]
        );
        
        // Gjej ose krijo subkategorinë Bateria
        $subcategory = Subcategory::firstOrCreate(
            ['slug' => 'bateria', 'category_id' => $category->id],
            [
                'category_id' => $category->id,
                'name' => 'Bateria',
                'slug' => 'bateria',
                'description' => 'Bateri të ndryshme për banjo dhe kuzhinë',
                'is_active' => true,
            ]
        );
        
        // Lista e të gjitha baterive (101 produkte)
        $faucets = [
            // Bateri Banjo
            ['Bateri Banjo Classic Chrome', 'Bateri banjo klasike me finish chrome të shndritshëm. Dizajn elegant dhe i qëndrueshëm.', 35.99, 49.99, 45],
            ['Bateri Banjo Modern Mat Black', 'Bateri banjo moderne me finish mat black. Rezistente ndaj ujit dhe njollave.', 49.99, 69.99, 40],
            ['Bateri Banjo Gold Edition', 'Bateri banjo luksoze me finish gold 24k. Për banjo ekskluzive.', 95.99, 139.99, 20],
            ['Bateri Banjo Brushed Nickel', 'Bateri banjo me finish brushed nickel. Rezistente ndaj gërvishtjeve.', 59.99, 79.99, 35],
            ['Bateri Banjo White Ceramic', 'Bateri banjo me doreza qeramike të bardha. Stil klasik.', 42.99, 59.99, 50],
            ['Bateri Banjo Termostatike', 'Bateri banjo me termostat për kontroll të temperaturës.', 89.99, 129.99, 25],
            ['Bateri Banjo me Kaskadë', 'Bateri banjo me ujë që rrjedh si kaskadë. Efekt luksoz.', 79.99, 109.99, 30],
            ['Bateri Banjo me Sensor', 'Bateri banjo automatike pa prekje dore. Higjienike.', 69.99, 99.99, 35],
            ['Bateri Banjo Të Gjatë', 'Bateri banjo me grykë të gjatë për lavamanë të mëdhenj.', 54.99, 74.99, 40],
            ['Bateri Banjo Të Shkurtër', 'Bateri banjo kompakte për lavamanë të vegjël.', 29.99, 39.99, 60],
            
            // Bateri Kuzhine
            ['Bateri Kuzhine Premium Chrome', 'Bateri kuzhine profesionale me spërkatës të integruar. Finish chrome.', 55.99, 79.99, 50],
            ['Bateri Kuzhine Mat Black', 'Bateri kuzhine moderne mat black me spërkatës me dy funksione.', 65.99, 89.99, 45],
            ['Bateri Kuzhine Gold', 'Bateri kuzhine luksoze gold për kuzhina premium.', 99.99, 149.99, 25],
            ['Bateri Kuzhine Brushed Steel', 'Bateri kuzhine inox brushed, rezistente ndaj gërvishtjeve.', 59.99, 79.99, 50],
            ['Bateri Kuzhine Industrial', 'Bateri kuzhine në stil industrial për kuzhina moderne.', 75.99, 99.99, 35],
            ['Bateri Kuzhine me Sensor', 'Bateri kuzhine automatike me sensor lëvizjeje.', 89.99, 129.99, 30],
            ['Bateri Kuzhine me Filtër', 'Bateri kuzhine me filtër uji të integruar.', 119.99, 169.99, 20],
            ['Bateri Kuzhine Pot Filler', 'Bateri kuzhine e montuar në mur për të mbushur tenxhere të mëdha.', 129.99, 179.99, 15],
            ['Bateri Kuzhine Commercial Grade', 'Bateri kuzhine për përdorim komercial, shumë e qëndrueshme.', 149.99, 199.99, 20],
            ['Bateri Kuzhine Retro', 'Bateri kuzhine në stil retro vintage.', 79.99, 109.99, 30],
            
            // Bateri Llavabo
            ['Bateri Llavabo Chrome', 'Bateri llavabo standarde me finish chrome.', 24.99, 34.99, 80],
            ['Bateri Llavabo Mat Black', 'Bateri llavabo moderne me finish mat black.', 34.99, 49.99, 70],
            ['Bateri Llavabo Wall Mount', 'Bateri llavabo e montuar në mur.', 39.99, 55.99, 60],
            ['Bateri Llavabo High Arc', 'Bateri llavabo me grykë të lartë për larje të lehtë.', 44.99, 64.99, 55],
            ['Bateri Llavabo Low Arc', 'Bateri llavabo kompakte me grykë të ulët.', 29.99, 39.99, 75],
            ['Bateri Llavabo me Dy Doreza', 'Bateri llavabo me dy doreza për kontroll të veçantë të ujit të nxehtë/ftohtë.', 49.99, 69.99, 45],
            ['Bateri Llavabo Single Handle', 'Bateri llavabo me një dorezë për kontroll të lehtë.', 32.99, 45.99, 65],
            ['Bateri Llavabo Waterfall', 'Bateri llavabo me efekt ujëvarë.', 59.99, 79.99, 35],
            ['Bateri Llavabo Sensor', 'Bateri llavabo automatike pa prekje dore.', 54.99, 74.99, 40],
            ['Bateri Llavabo Vintage', 'Bateri llavabo në stil vintage me bronz.', 69.99, 94.99, 30],
        ];
        
        // Gjenero 101 produkte (përsërit dhe modifiko listën)
        $products = [];
        for ($i = 1; $i <= 101; $i++) {
            // Zgjidh një bateri nga lista ose krijo të re
            if ($i <= count($faucets)) {
                $faucet = $faucets[$i - 1];
                $name = $faucet[0];
                $description = $faucet[1];
                $price = $faucet[2];
                $comparePrice = $faucet[3];
                $stock = $faucet[4];
            } else {
                // Për numrat mbi 30, krijo emra të rinj
                $types = ['Chrome', 'Mat Black', 'Brushed Nickel', 'Gold', 'Bronze', 'White', 'Steel', 'Copper'];
                $styles = ['Classic', 'Modern', 'Premium', 'Deluxe', 'Standard', 'Executive', 'Luxury', 'Elite'];
                $places = ['Banjo', 'Kuzhine', 'Llavabo', 'Dush', 'Bide', 'Lavaman', 'Oborr', 'Garazh'];
                
                $type = $types[array_rand($types)];
                $style = $styles[array_rand($styles)];
                $place = $places[array_rand($places)];
                $name = "Bateri {$place} {$style} {$type} - Modeli {$i}";
                
                $description = "Bateri profesionale {$place} me finish {$type}. " .
                              "Dizajn {$style}, material cilësor, rezistente ndaj korrozionit. " .
                              "Garanci 5 vjet për defektet e prodhimit.";
                
                $price = rand(25, 150) + 0.99;
                $comparePrice = rand(35, 200) + 0.99;
                $stock = rand(10, 100);
            }
            
            // Krijo slug unik
            $slug = Str::slug($name) . '-' . uniqid();
            
            // Krijo SKU
            $sku = 'BAT-' . str_pad($i, 4, '0', STR_PAD_LEFT);
            
            $products[] = [
                'subcategory_id' => $subcategory->id,
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'price' => $price,
                'compare_price' => $comparePrice,
                'stock' => $stock,
                'sku' => $sku,
                'image' => 'products/faucet-' . rand(1, 10) . '.jpg',
                'gallery' => json_encode(['product1.jpg', 'product2.jpg', 'product3.jpg']),
                'attributes' => json_encode([
                    'finish' => ['Chrome', 'Mat Black', 'Brushed Nickel'][array_rand(['Chrome', 'Mat Black', 'Brushed Nickel'])],
                    'installation' => ['Deck Mount', 'Wall Mount'][array_rand(['Deck Mount', 'Wall Mount'])],
                    'handles' => ['Single Handle', 'Two Handles'][array_rand(['Single Handle', 'Two Handles'])],
                ]),
                'is_active' => true,
                'is_featured' => ($i <= 20) ? true : false,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        
        // Insert all products
        foreach ($products as $product) {
            Product::create($product);
        }
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('✅ Created ' . count($products) . ' bathroom faucet products!');
        $this->command->info('📊 Category: ' . $category->name);
        $this->command->info('📁 Subcategory: ' . $subcategory->name);
    }
}