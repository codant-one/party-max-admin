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
        Schema::table('home_images', function (Blueprint $table) {
            $table->longText('title')->nullable()->after('url');
            $table->longText('text')->nullable()->after('title');
            $table->string('button_text')->nullable()->after('text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('home_images', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('text');
            $table->dropColumn('button_text');
        });
    }
};
