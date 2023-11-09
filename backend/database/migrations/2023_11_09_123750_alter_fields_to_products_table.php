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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedBigInteger('brand_id')->after('user_id')->nullable();
            $table->unsignedBigInteger('state_id')->after('brand_id')->default(3);

            $table->dropColumn('sku');
            $table->longText('single_description')->nullable()->after('name');
            $table->string('wholesale_price')->nullable()->after('price_for_sale');
            $table->dateTime('estimated_delivery_time')->nullable()->after('image');

            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('sku');
            $table->dropColumn('single_description');
            $table->dropColumn('wholesale_price');
            $table->dropColumn('estimated_delivery_time');
            $table->dropColumn('brand_id');
            $table->dropColumn('state_id');
        });
    }
};
