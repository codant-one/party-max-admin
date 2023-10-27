<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

use App\Models\Blog;
use App\Models\BlogCategory;

use Spatie\Permission\Models\Permission;

class BlogCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = ['Productos', 'Ventas', 'Clientes', 'Proveedores'];
        $icon = ['mdi-cart', 'tabler-discount-2', 'mdi-account-star', 'mdi-account-tie'];
        $color = ['primary', 'success', 'info', 'warning'];

        foreach($categories as $key => $category){
            BlogCategory::create([
                'name' => $category,
                'slug' => Str::slug($category),
                'icon' => $icon[$key],
                'color' => $color[$key]
            ]);
        }

        if (!file_exists(storage_path('app/public/blogs'))) {
            mkdir(storage_path('app/public/blogs'), 0755,true);
        } //create a folder

        $file = new Filesystem;
        $file->cleanDirectory('storage/app/public/blogs');

        Blog::factory()->count(10)->create();
        
        Permission::create(['name' => 'ver categorías-blogs']);
        Permission::create(['name' => 'crear categorías-blogs']);
        Permission::create(['name' => 'editar categorías-blogs']);
        Permission::create(['name' => 'eliminar categorías-blogs']);
        Permission::create(['name' => 'ver página-blogs']);
    }
}
