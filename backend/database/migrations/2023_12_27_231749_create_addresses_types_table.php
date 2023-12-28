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
        Schema::create('addresses_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->timestamps();
        });

        DB::table('addresses_types')->insert([
            [
                'name' => 'Hogar',
                'description' => 'Hora de entrega (7 a.m. - 9 p.m.)',
                'created_at' => now(),
                'updated_at' => now() 
            ],
            [
                'name' => 'Oficina',
                'description' => 'Hora de entrega (10 a.m. - 6 p.m.)',
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
        Schema::dropIfExists('addresses_types');
    }
};
