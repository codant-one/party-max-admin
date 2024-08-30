<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission;

class CalendarSeeder extends Seeder
{
    public function run()
    {
        Permission::create(['name' => 'ver calendario']);        
    }
}