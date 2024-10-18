<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Permission;

class ReferralPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'ver stock']);
        Permission::create(['name' => 'editar stock']);
        Permission::create(['name' => 'ver remisiones']);
        Permission::create(['name' => 'editar remisiones']);
    }
}
