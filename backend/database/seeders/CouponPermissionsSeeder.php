<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Permission;

class CouponPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'ver cupones']);
        Permission::create(['name' => 'crear cupones']);
        Permission::create(['name' => 'editar cupones']);
        Permission::create(['name' => 'eliminar cupones']);
    }
}
