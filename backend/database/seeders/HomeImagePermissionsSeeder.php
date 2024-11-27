<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Permission;

class HomeImagePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'ver home-imágenes']);
        Permission::create(['name' => 'crear home-imágenes']);
        Permission::create(['name' => 'editar home-imágenes']);
        Permission::create(['name' => 'eliminar home-imágenes']);
    }
}
