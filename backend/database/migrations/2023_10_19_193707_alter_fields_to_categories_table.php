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
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->renameColumn('image', 'banner');
            $table->string('banner_2')->nullable()->after('image');
            $table->string('banner_3')->nullable()->after('banner_2');
            $table->string('banner_4')->nullable()->after('banner_3');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->longtext('description')->nullable();
            $table->renameColumn('banner', 'image'); 
            $table->dropColumn('banner_2');
            $table->dropColumn('banner_3');
            $table->dropColumn('banner_4');
        });
    }
};
