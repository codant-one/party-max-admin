<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

use App\Models\Flavor;
use App\Models\Filling;
use App\Models\Cupcake;
use App\Models\CakeType;
use App\Models\CakeSize;

class CupcakeSeeder extends Seeder
{
    public function run()
    {
        $flavors = [
            [
                'id' => 1,
                'name' => 'Vainilla',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Milkyway',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'Naranja semilla amapola',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'name' => 'Vainilla amapola',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'name' => 'Redvelvet',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 6,
                'name' => 'Envinado',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 7,
                'name' => 'Coco',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 8,
                'name' => 'Arándanos',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
       
        Flavor::insert($flavors);

        $fillings = [
            [
                'id' => 1,
                'name' => 'Frutos Rojos',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Fresa',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'name' => 'Arequipe',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'name' => 'Chocoarequipe',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'name' => 'Mousse de queso crema',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 6,
                'name' => 'Sin relleno',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
       
        Filling::insert($fillings);

        $cakeTypes = [
            [
                'id' => 1,
                'name' => 'Cupcake 90gr',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'name' => 'Porción de torta',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
       
        CakeType::insert($cakeTypes);

        $cakeSizes = [
            [
                'id' => 1,
                'cake_type_id' => 1,
                'name' => 'Caja de 6 cupcakes',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 2,
                'cake_type_id' => 1,
                'name' => 'Caja de 12 cupcakes',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 3,
                'cake_type_id' => 2,
                'name' => '10px',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 4,
                'cake_type_id' => 2,
                'name' => '20px',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 5,
                'cake_type_id' => 2,
                'name' => '30px',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 6,
                'cake_type_id' => 2,
                'name' => '40px',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'id' => 7,
                'cake_type_id' => 2,
                'name' => '50px',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];
       
        CakeSize::insert($cakeSizes);

        Permission::create(['name' => 'ver atributos']);
        Permission::create(['name' => 'crear atributos']);
        Permission::create(['name' => 'editar atributos']);
        Permission::create(['name' => 'eliminar atributos']);
        
    }
}