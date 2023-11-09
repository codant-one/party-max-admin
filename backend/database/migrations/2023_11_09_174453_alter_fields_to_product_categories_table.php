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
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id']);
            $table->unsignedBigInteger('product_image_id')->nullable()->first();

            $table->foreign('product_image_id')->references('id')->on('product_images')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('product_image_id');
        });
    }
};
