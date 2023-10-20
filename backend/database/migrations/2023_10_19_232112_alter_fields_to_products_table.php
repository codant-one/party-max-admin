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
            
            $table->unsignedBigInteger('user_id')->nullable()->after('id')->default(1);
            $table->integer('sales')->nullable()->after('stock')->default(0);
            $table->float('rating')->nullable()->after('sales')->default(0);

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            
            $table->dropColumn('user_id');
            $table->dropColumn('sales');
            $table->dropColumn('rating');
        });
    }
};
