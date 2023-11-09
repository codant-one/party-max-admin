<?php

namespace Database\Seeders;

use App\Models\State;
use Illuminate\Database\Seeder;

class StateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $states = ['Inactivo', 'Activo', 'En Stock', 'Agotado'];

        foreach($states as $state){
            State::create([
                'name' => $state,
                'label' => strtolower($state)
            ]);
        }

    }
}
