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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->default(1);
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('state_id')->default(3);
            $table->unsignedBigInteger('order_id')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->longText('single_description')->nullable();
            $table->longtext('description')->nullable();
            $table->string('sku')->nullable();
            $table->float('price', 10, 2);
            $table->integer('sales')->nullable()->default(0);
            $table->float('rating')->nullable()->default(0);
            $table->string('image')->nullable();
            $table->dateTime('estimated_delivery_time')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
