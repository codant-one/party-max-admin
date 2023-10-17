<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Faq;
use Spatie\Permission\Models\Permission;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::create(['name' => 'ver faqs']);
        Permission::create(['name' => 'crear faqs']);
        Permission::create(['name' => 'editar faqs']);
        Permission::create(['name' => 'eliminar faqs']);
    }
}
