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
        Schema::create('cake_sizes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cake_type_id');
            $table->string('name');
            $table->timestamps();

            $table->foreign('cake_type_id')->references('id')->on('cake_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cake_sizes');
    }
};
