<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\Models\Permission;

class BannerPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'ver banners']);
        Permission::create(['name' => 'crear banners']);
        Permission::create(['name' => 'editar banners']);
        Permission::create(['name' => 'eliminar banners']);
    }
}
