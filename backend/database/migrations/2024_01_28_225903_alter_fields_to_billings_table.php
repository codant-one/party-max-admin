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
            $table->dropColumn('default');
            $table->string('name')->nullable()->after('cvv_code');
            $table->string('last_name')->nullable()->after('name');
            $table->string('company')->nullable()->after('last_name');
            $table->string('email')->nullable()->after('company');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('billings', function (Blueprint $table) {
            $table->tinyInteger('default')->default(0);
            $table->dropColumn('name');
            $table->dropColumn('last_name');
            $table->dropColumn('company');
            $table->dropColumn('email');
        });
    }
};
