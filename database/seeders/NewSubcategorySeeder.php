<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class NewSubcategorySeeder extends Seeder
{
    public function run(): void
    {
        $subcategories = [
            'Tusha' => [
                'Bateria' => 'Bateri të ndryshme për banjo dhe kuzhinë',
                'Bateri oborri' => 'Bateri të jashtme për kopsht dhe oborr',
                'Gypa' => 'Gypa dhe tubacione për ujë',
                'Bateria te llavabos' => 'Bateri speciale për lavaman',
            ],
            'Vegla Pune' => [
                'Kaqavia' => 'Kaqavi manuale dhe elektrike',
                'Qelsa te ndryshem' => 'Çelësa të ndryshëm për punëtori',
                'Diska' => 'Diska prerëse dhe bluarëse',
                'Shajme' => 'Shajma dhe lecka për pastrim',
                'Brusha' => 'Brusha dhe furça për lyerje',
                'Valaka' => 'Valaka dhe vizore matëse',
                'Sharra' => 'Sharra të ndryshme për dru dhe metal',
                'Qekiq' => 'Çekiçë për punë të ndryshme',
            ],
            'Vegla Kopshti' => [
                'Lopata' => 'Lopata për gërmim dhe punime toke',
                'Pirunje' => 'Pirunë për punimin e tokës',
                'Grebuja' => 'Grebuja për rrafshimin e tokës',
                'Draper' => 'Drapera dhe grabujë për gjethe',
                'Karroca' => 'Karrocë dore për transport',
            ],
            'Elektronike' => [
                'Motorra te druve' => 'Motorrë elektrikë për dru',
                'Brusalica' => 'Brusalicë për bluarje dhe prerje',
                'Burmashina' => 'Burmashinë për shpime',
                'Makina gleti' => 'Makina për glet dhe rrumbullakim',
                'Llampa' => 'Llampa pune dhe ndriçim',
                'Shtekera' => 'Shtekera dhe gërshërë elektrike',
            ],
            'Ujësjellës' => [
                'Lidhesa metali' => 'Lidhësa metalikë për tuba',
                'Lidhesa plastike' => 'Lidhësa plastikë për tuba',
                'Lidhesa te bardha' => 'Lidhësa të bardhë për instalime',
                'Teflona' => 'Shirit teflon për vulosje',
            ],
        ];
        
        foreach ($subcategories as $categoryName => $subs) {
            $category = Category::where('name', $categoryName)->first();
            
            if ($category) {
                foreach ($subs as $subName => $description) {
                    Subcategory::create([
                        'category_id' => $category->id,
                        'name' => $subName,
                        'slug' => Str::slug($subName),
                        'description' => $description,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }
}