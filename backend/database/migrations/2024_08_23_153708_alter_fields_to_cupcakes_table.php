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
        Schema::table('cupcakes', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn(['category_id']);

            $table->dropForeign(['flavor_id']);
            $table->dropColumn(['flavor_id']);

            $table->dropForeign(['filling_id']);
            $table->dropColumn(['filling_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cupcakes', function (Blueprint $table) {
            //
        });
    }
};