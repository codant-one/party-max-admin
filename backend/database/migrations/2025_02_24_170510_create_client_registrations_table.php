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
        Schema::create('client_registrations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ip_id');
            $table->string('response_code_pol');
            $table->string('message');
            $table->timestamp('date');
            $table->timestamps();

            $table->foreign('ip_id')->references('id')->on('client_ips')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_registrations');
    }
};
