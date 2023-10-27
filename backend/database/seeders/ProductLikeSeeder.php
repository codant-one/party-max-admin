<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Product;
use App\Models\User;
use App\Models\ProductLike;

use Spatie\Permission\Models\Permission;


class ProductLikeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        ProductLike::factory(10)->create();
    }

    
}
