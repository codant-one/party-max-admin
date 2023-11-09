<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Brand;

class BrandSeeder extends Seeder
{
    public function run()
    {
        $brands = [
            [
                'name' => 'Galaxy',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Apple',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
       

        Brand::insert($brands);
    }
}