<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            PermissionSeeder::class,
            CountrySeeder::class,
            ProvinceSeeder::class,
            AdminSeeder::class,

            StateSeeder::class,
            GenderSeeder::class,
            ClientSeeder::class,

            CategorySeeder::class,
            ServiceSeeder::class,

            ColorSeeder::class,
            BrandSeeder::class,
            ProductSeeder::class,
            ProductLikeSeeder::class,

            BlogSeeder::class,
            FaqSeeder::class,
            FaqCategorySeeder::class,
            BlogCategorySeeder::class,

            MoreStateSeeder::class,
            MorePermissionSeeder::class,

            AddressSeeder::class,

            OrderSeeder::class,
            SupplierSeeder::class,

            DocumentTypeSeeder::class,
            
            OrderPermissionSeeder::class,

            ShipmentPermissionSeeder::class,
            OrdersPermissionSeeder::class
        ]);

    }
}
