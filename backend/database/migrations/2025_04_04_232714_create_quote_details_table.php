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
        Schema::create('quote_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quote_id');
            $table->unsignedBigInteger('product_color_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->unsignedBigInteger('cake_size_id')->nullable();
            $table->unsignedBigInteger('flavor_id')->nullable();
            $table->unsignedBigInteger('filling_id')->nullable();
            $table->string('price');
            $table->string('quantity');
            $table->string('total');
            $table->timestamps();

            $table->foreign('quote_id')->references('id')->on('quotes')->onDelete('cascade');
            $table->foreign('product_color_id')->references('id')->on('product_colors')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('cake_size_id')->references('id')->on('cake_sizes')->onDelete('cascade');
            $table->foreign('flavor_id')->references('id')->on('flavors')->onDelete('cascade');
            $table->foreign('filling_id')->references('id')->on('fillings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_details');
    }
};
