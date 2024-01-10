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
        Schema::create('payment_states', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('label')->default("");
            $table->timestamps();
        });

        DB::table('payment_states')->insert([
            [
                'name' => 'Pendiente',
                'label' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now() 
            ],
            [
                'name' => 'Cancelado',
                'label' => 'cancelado',
                'created_at' => now(),
                'updated_at' => now() 
            ],
            [
                'name' => 'Fallido',
                'label' => 'fallido',
                'created_at' => now(),
                'updated_at' => now() 
            ],
            [
                'name' => 'Pagada',
                'label' => 'pagada',
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
        Schema::dropIfExists('payment_states');
    }
};
