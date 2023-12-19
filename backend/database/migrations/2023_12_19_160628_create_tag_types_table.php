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
        Schema::create('tag_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });

        DB::table('tag_types')->insert([
            [
                'name' => 'Productos',
                'description' => 'Tipo de tag para el modulo de productos',
                'created_at' => now(),
                'updated_at' => now() 
            ],
            [
                'name' => 'Blogs',
                'description' => 'Tipo de tag para el modulo de blogs',
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
        Schema::dropIfExists('tag_types');
    }
};
