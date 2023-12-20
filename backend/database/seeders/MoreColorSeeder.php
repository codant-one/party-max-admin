<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

use App\Models\Color;

class MoreColorSeeder extends Seeder
{
    public function run()
    {
        $colors = [
            [
                'name' => 'Oro rosa',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Neón',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Metalizado',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
       

        Color::insert($colors);
    }
}