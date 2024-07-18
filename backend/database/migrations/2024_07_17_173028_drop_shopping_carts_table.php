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
        Schema::dropIfExists('shopping_carts');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('shopping_carts', function (Blueprint $table) {
            $table->unsignedBigInteger('product_color_id');
            $table->unsignedBigInteger('client_id');
            $table->integer('quantity');
            $table->tinyInteger('wholesale')->default(0);
            $table->timestamps();

            $table->foreign('product_color_id')->references('id')->on('product_colors')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }
};
