<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->unsignedBigInteger('invoice_id')->default(0)->after('state_id');
        });

        // Backfill per-user sequential invoice_id based on creation order
        // Uses MySQL 8 window functions
        DB::statement(
            'UPDATE invoices i
             JOIN (
                SELECT id, ROW_NUMBER() OVER (PARTITION BY user_id ORDER BY id) AS rn
                FROM invoices
             ) t ON t.id = i.id
             SET i.invoice_id = t.rn'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('invoice_id');
        });
    }
};