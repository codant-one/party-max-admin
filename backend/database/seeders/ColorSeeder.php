<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Color;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ColorSeeder extends Seeder
{
    public function run()
    {
        $colors = [
            [
                'name' => 'Azul',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Amarillo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Rojo',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Verde',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Gris',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Negro',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Blanco',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Morado',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Rosado',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Naranja',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
       

        Color::insert($colors);
    }
}