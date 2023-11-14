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
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id']);

            $table->dropForeign(['color_id']);
            $table->dropColumn(['color_id']);

            $table->dropColumn(['sku']);
            
            $table->unsignedBigInteger('product_color_id')->nullable()->after('id');

            $table->foreign('product_color_id')->references('id')->on('product_colors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('product_color_id');
        });
    }
};
