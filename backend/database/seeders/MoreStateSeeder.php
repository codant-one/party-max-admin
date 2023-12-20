<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class MoreStateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        State::insert([
            [
                'name' => 'Rechazado',
                'label' => 'rechazado',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);

    }
}
