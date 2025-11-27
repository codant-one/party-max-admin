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
        Schema::table('invoices', function (Blueprint $table) {
            // Subtotal de productos y servicios
            $table->decimal('total_products', 10, 2)->default(0.00)->after('total');
            $table->decimal('total_services', 10, 2)->default(0.00)->after('total_products');
            
            // Comisión por productos (porcentaje y monto)
            $table->decimal('products_commission_percentage', 5, 2)->default(0.00)->after('total_services');
            $table->decimal('products_commission_amount', 10, 2)->default(0.00)->after('products_commission_percentage');
            
            // Comisión por servicios (porcentaje y monto)
            $table->decimal('services_commission_percentage', 5, 2)->default(0.00)->after('products_commission_amount');
            $table->decimal('services_commission_amount', 10, 2)->default(0.00)->after('services_commission_percentage');
            
            // Monto total de la factura (suma de productos - comisión productos + servicios - comisión servicios)
            $table->decimal('total_amount', 10, 2)->default(0.00)->after('services_commission_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'total_products',
                'total_services',
                'products_commission_percentage',
                'products_commission_amount',
                'services_commission_percentage',
                'services_commission_amount',
                'total_amount'
            ]);
        });
    }
};