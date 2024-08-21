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
        Schema::table('services', function (Blueprint $table) {
            $table->boolean('favourite')->default(0)->after('image');
            $table->boolean('archived')->default(0)->after('favourite');
            $table->boolean('discarded')->default(0)->after('archived');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('favourite');
            $table->dropColumn('archived');
            $table->dropColumn('discarded');

        });
    }
};
