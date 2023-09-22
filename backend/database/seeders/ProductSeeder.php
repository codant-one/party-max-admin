<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Seeder;

use App\Models\ProductDetail;
use App\Models\ProductImage;
use App\Models\Product;
use App\Models\ProductCategory;
use Spatie\Permission\Models\Permission;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $file = new Filesystem;
        $file->cleanDirectory('storage/app/public/products/gallery');

        if (!file_exists(storage_path('app/public/products/gallery'))) {
            mkdir(storage_path('app/public/products/gallery'), 0755,true);
        } //create a folder

        $file = new Filesystem;
        $file->cleanDirectory('storage/app/public/products/main');

        if (!file_exists(storage_path('app/public/products/main'))) {
            mkdir(storage_path('app/public/products/main'), 0755,true);
        } //create a folder

        Product::factory(10)->create();

        $products = Product::all();
        
        foreach ($products as $product) {
            ProductCategory::factory(['product_id' => $product->id])->create();
            ProductDetail::factory(['product_id' => $product->id])->create();
            ProductImage::factory(['product_id' => $product->id])->create();
        }

        Permission::create(['name' => 'ver productos']);
        Permission::create(['name' => 'crear productos']);
        Permission::create(['name' => 'editar productos']);
        Permission::create(['name' => 'eliminar productos']);
    }
}