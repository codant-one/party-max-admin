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
        Schema::create('shipping_states', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('label')->default("");
            $table->timestamps();
        });

        DB::table('shipping_states')->insert([
            [
                'name' => 'Listo para enviar',
                'label' => 'listo',
                'created_at' => now(),
                'updated_at' => now() 
            ],
            [
                'name' => 'Fuera de entrega',
                'label' => 'fuera',
                'created_at' => now(),
                'updated_at' => now() 
            ],
            [
                'name' => 'Entregado',
                'label' => 'entregado',
                'created_at' => now(),
                'updated_at' => now() 
            ],
            [
                'name' => 'Enviado',
                'label' => 'enviado',
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
        Schema::dropIfExists('shipping_states');
    }
};
