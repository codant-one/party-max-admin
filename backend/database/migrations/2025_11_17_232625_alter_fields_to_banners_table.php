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
        Schema::table('banners', function (Blueprint $table) {
            $table->string('url')->nullable()->after('banner_4');
            $table->string('url_2')->nullable()->after('url');
            $table->string('url_3')->nullable()->after('url_2');
            $table->string('url_4')->nullable()->after('url_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('url');
            $table->dropColumn('url_2');
            $table->dropColumn('url_3');
            $table->dropColumn('url_4');
        });
    }
};
