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
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('type_account');
            $table->dropColumn('name_bank');
            $table->dropColumn('bank_account');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->tinyInteger('type_account')->after('main_document')->default(0);
            $table->string('name_bank')->nullable()->after('type_account');
            $table->string('bank_account')->nullable();
        });
    }
};
