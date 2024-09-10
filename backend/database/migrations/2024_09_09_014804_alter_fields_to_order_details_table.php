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
            $table->unsignedBigInteger('cake_size_id')->nullable()->after('service_id');
            $table->unsignedBigInteger('flavor_id')->nullable()->after('cake_size_id');
            $table->unsignedBigInteger('filling_id')->nullable()->after('flavor_id');

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
        Schema::table('order_details', function (Blueprint $table) {
            $table->dropColumn('cake_size_id');
            $table->dropColumn('flavor_id');
            $table->dropColumn('filling_id');
        });
    }
};
