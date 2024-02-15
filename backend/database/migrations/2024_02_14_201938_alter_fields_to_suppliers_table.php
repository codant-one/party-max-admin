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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->string('company_name')->nullable()->after('gender_id');
            $table->string('phone_contact')->nullable()->after('birthday');
            $table->unsignedBigInteger('gender_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropColumn('company_name');
            $table->dropColumn('phone_contact');
            $table->dropColumn('gender_id');
        });
    }
};
