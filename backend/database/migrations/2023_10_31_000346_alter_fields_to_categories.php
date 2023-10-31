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
        Schema::table('categories', function (Blueprint $table) {

            $table->unsignedBigInteger('banner_category_id')->nullable()->after('category_id');
            $table->unsignedBigInteger('banner2_category_id')->nullable()->after('banner_category_id');
            $table->unsignedBigInteger('banner3_category_id')->nullable()->after('banner2_category_id');
            $table->unsignedBigInteger('banner4_category_id')->nullable()->after('banner3_category_id');
            
            $table->foreign('banner_category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('banner2_category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('banner3_category_id')->references('id')->on('categories')->onDelete('cascade');
            $table->foreign('banner4_category_id')->references('id')->on('categories')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('banner_category_id');
            $table->dropColumn('banner2_category_id');
            $table->dropColumn('banner3_category_id');
            $table->dropColumn('banner4_category_id');
        });
    }
};
