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
            $table->unsignedBigInteger('document_type_id')->nullable()->after('id');
            $table->string('main_document')->nullable()->after('document_type_id');
            $table->tinyInteger('type_account')->after('main_document')->default(0);
            $table->string('name_bank')->nullable()->after('type_account');
            $table->string('ncc')->nullable()->after('name_bank');
            $table->dropColumn('nit');
            $table->dropColumn('rut');

            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('document_type_id');
            $table->dropColumn('main_document');
            $table->dropColumn('type_account');
            $table->dropColumn('name_bank');
            $table->dropColumn('ncc');
            $table->string('nit')->nullable();
            $table->string('rut')->nullable();

        });
    }
};
