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
        Schema::table('order_details', function (Blueprint $table) {
            $table->unsignedBigInteger('product_color_id')->nullable()->change();
            $table->unsignedBigInteger('service_id')->nullable()->after('product_color_id');
            $table->timestamp('date')->nullable()->after('total');

            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_details', function (Blueprint $table) {
            $table->unsignedBigInteger('product_color_id');
            $table->dropColumn('service_id');
            $table->dropColumn('date');
        });
    }
};
