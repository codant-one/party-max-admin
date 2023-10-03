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
            
            CategorySeeder::class,

            ColorSeeder::class,
            ProductSeeder::class,

            BlogSeeder::class,
        ]);

    }
}
