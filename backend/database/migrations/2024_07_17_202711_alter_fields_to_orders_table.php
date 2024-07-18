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
        Schema::table('orders', function (Blueprint $table) {
            $table->unsignedBigInteger('address_id')->nullable()->change();

            $table->unsignedBigInteger('addresses_type_id')->nullable()->after('address_id');
            $table->unsignedBigInteger('province_id')->nullable()->after('address_id');
            $table->string('shipping_phone')->nullable()->after('total');
            $table->longtext('shipping_address')->nullable()->after('shipping_phone');
            $table->text('shipping_street')->nullable()->after('shipping_address');
            $table->string('shipping_city')->nullable()->after('shipping_street');
            $table->string('shipping_postal_code', 20)->nullable()->after('shipping_city');

            $table->foreign('addresses_type_id')->references('id')->on('addresses_types')->onDelete('cascade');
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('address_id');
            $table->dropColumn('addresses_type_id');
            $table->dropColumn('province_id');
            $table->dropColumn('shipping_phone');
            $table->dropColumn('shipping_address');
            $table->dropColumn('shipping_street');
            $table->dropColumn('shipping_city');
            $table->dropColumn('shipping_postal_code');
        });
    }
};
