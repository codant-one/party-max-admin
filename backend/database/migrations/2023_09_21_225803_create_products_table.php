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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->longtext('description')->nullable();
            $table->string('sku');
            $table->string('price');
            $table->string('price_for_sale');
            $table->string('stock');
            $table->string('slug');
            $table->boolean('favourite')->default(0);
            $table->boolean('archived')->default(0);
            $table->boolean('discarded')->default(0);
            $table->string('image');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
