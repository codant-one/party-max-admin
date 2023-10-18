<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Blog;
use Spatie\Permission\Models\Permission;

class BlogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     */
    public function run(): void
    {
        Permission::create(['name' => 'ver blogs']);
        Permission::create(['name' => 'crear blogs']);
        Permission::create(['name' => 'editar blogs']);
        Permission::create(['name' => 'eliminar blogs']);
    }
}
