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
        Schema::table('supplier_accounts', function (Blueprint $table) {
            $table->decimal('service_sales_amount', 10, 2)->nullable()->after('wholesale_sales_amount'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_accounts', function (Blueprint $table) {
            $table->dropColumn('service_sales_amount');
        });
    }
};
