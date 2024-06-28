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
        Schema::table('billings', function (Blueprint $table) {
            $table->string('reference_pol')->nullable()->after('province_id');
            $table->tinyInteger('nequi')->default(0)->after('reference_pol');
            $table->string('pse_bank')->nullable()->after('pse');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->dropColumn('reference_pol');
            $table->dropColumn('nequi');
            $table->dropColumn('pse_bank');
        });
    }
};
