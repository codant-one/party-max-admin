<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Permission;

class ServicePermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'ver servicios']);
        Permission::create(['name' => 'crear servicios']);
        Permission::create(['name' => 'editar servicios']);
        Permission::create(['name' => 'eliminar servicios']);

        Permission::create(['name' => 'ver marcas-servicios']);
        Permission::create(['name' => 'crear marcas-servicios']);
        Permission::create(['name' => 'editar marcas-servicios']);
        Permission::create(['name' => 'eliminar marcas-servicios']);

        Permission::create(['name' => 'ver tag-servicios']);
        Permission::create(['name' => 'crear tag-servicios']);
        Permission::create(['name' => 'editar tag-servicios']);
        Permission::create(['name' => 'eliminar tag-servicios']);

        Permission::create(['name' => 'ver ordenar-servicios']);
        Permission::create(['name' => 'ver servicios-pendientes']);
        Permission::create(['name' => 'aprobar servicios']);
        Permission::create(['name' => 'rechazar servicios']);

    }
}
