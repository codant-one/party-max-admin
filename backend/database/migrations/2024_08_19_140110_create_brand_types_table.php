<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('brand_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        DB::table('brand_types')->insert([
            [
                'name' => 'Productos',
                'created_at' => now(),
                'updated_at' => now() 
            ],
            [
                'name' => 'Servicios',
                'created_at' => now(),
                'updated_at' => now() 
            ]
        ]);

        DB::table('tag_types')->insert([
            [
                'name' => 'Servicios',
                'description' => 'Tipo de tag para el modulo de servicios',
                'created_at' => now(),
                'updated_at' => now() 
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('brand_types');
    }
};
