<?php

namespace Database\Seeders;

use App\Models\Gender;
use Illuminate\Database\Seeder;

class GenderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $genders = [ 
            [
                'name' => 'Femenino',
                'code' => 'F',
            ],
            [
                'name' => 'Masculino',
                'code' => 'M',
            ],
            [
                'name' => 'Otros',
                'code' => 'O',
            ]
        ];

        Gender::insert($genders);
    }
}
