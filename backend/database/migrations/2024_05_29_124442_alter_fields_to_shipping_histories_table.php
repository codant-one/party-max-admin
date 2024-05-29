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
        Schema::table('shipping_histories', function (Blueprint $table) {
            $table->string('reason')->nullable()->after('shipping_state_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shipping_histories', function (Blueprint $table) {
            $table->dropColumn('reason');
        });
    }
};
