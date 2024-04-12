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
                'id' => 1,
                'name' => 'Azul',
                'color' => '#0000FF',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Amarillo',
                'color' => '#FFFF00',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'Rojo',
                'color' => '#FF0000',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'name' => 'Verde',
                'color' => '#008000',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'name' => 'Gris',
                'color' => '#808080',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 6,
                'name' => 'Negro',
                'color' => '#000000',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 7,
                'name' => 'Blanco',
                'color' => '#FFFFFF',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 8,
                'name' => 'Morado',
                'color' => '#800080',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 9,
                'name' => 'Rosado',
                'color' => '#FFC0CB',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 10,
                'name' => 'Naranja',
                'color' => '#FFA500',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 11,
                'name' => 'Multicolor',
                'color' => 'linear-gradient(to right, #FF0000, #0000FF, #FFA500, #FFFF00, #008000)',
                'is_gradient' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 12,
                'name' => 'Plateado',
                'color' => 'linear-gradient(to right, #C0C0C0, #FFFFFF, #C0C0C0)',
                'is_gradient' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 13,
                'name' => 'Dorado',
                'color' => 'linear-gradient(to right, #C0C0C0, #FFD700, #FFD700, #C0C0C0)',
                'is_gradient' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 14,
                'name' => 'Oro rosa',
                'color' => 'linear-gradient(to right, #F3D2D4, #FDF6F6, #DE8489, #EDBCBF)',
                'is_gradient' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 15,
                'name' => 'Neón',
                'color' => 'linear-gradient(to right, #00FF1E, #FFFFFF, #00FF1E)',
                'is_gradient' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 16,
                'name' => 'Metalizado',
                'color' => 'linear-gradient(to right, #C0C0C0, #FFFFFF, #FFFFFF, #C0C0C0)',
                'is_gradient' => 1,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 17,
                'name' => 'Ninguno',
                'color' => null,
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 18,
                'name' => 'Lila',
                'color' => '#C8A2C8',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 19,
                'name' => 'Fucsia',
                'color' => '#FF00FF',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 20,
                'name' => 'Azul Celeste',
                'color' => '#87CEEB',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 21,
                'name' => 'Azul Caribe',
                'color' => '#1E90FF',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 22,
                'name' => 'Verde lima',
                'color' => '#00FF00',
                'is_gradient' => 0,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
       

        Color::insert($colors);
    }
}