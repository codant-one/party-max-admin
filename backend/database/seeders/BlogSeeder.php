<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Filesystem\Filesystem;

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
        if (!file_exists(storage_path('app/public/blogs'))) {
            mkdir(storage_path('app/public/blogs'), 0755,true);
        } //create a folder

        $file = new Filesystem;
        $file->cleanDirectory('storage/app/public/blogs');

        Blog::factory()->count(10)->create();

        Permission::create(['name' => 'ver blogs']);
        Permission::create(['name' => 'crear blogs']);
        Permission::create(['name' => 'editar blogs']);
        Permission::create(['name' => 'eliminar blogs']);
    }
}
