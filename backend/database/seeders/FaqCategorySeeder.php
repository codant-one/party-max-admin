<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

use App\Models\Faq;
use App\Models\FaqCategory;

use Spatie\Permission\Models\Permission;

class FaqCategorySeeder extends Seeder
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
            FaqCategory::create([
                'name' => $category,
                'slug' => Str::slug($category),
                'icon' => $icon[$key],
                'color' => $color[$key]
            ]);
        }

        Faq::factory()->count(10)->create();

        
        Permission::create(['name' => 'ver categorías-faqs']);
        Permission::create(['name' => 'crear categorías-faqs']);
        Permission::create(['name' => 'editar categorías-faqs']);
        Permission::create(['name' => 'eliminar categorías-faqs']);
        Permission::create(['name' => 'ver página-faqs']);
    }
}
