<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

use App\Models\Color;

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
            ],
            [
                'name' => 'Multicolor',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Plateado',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Dorado',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
       

        Color::insert($colors);
    }
}