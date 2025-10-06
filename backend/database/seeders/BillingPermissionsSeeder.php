<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Permission;

class BillingPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'ver facturas']);
        Permission::create(['name' => 'crear facturas']);
        Permission::create(['name' => 'editar facturas']);
        Permission::create(['name' => 'eliminar facturas']);
    }
}
