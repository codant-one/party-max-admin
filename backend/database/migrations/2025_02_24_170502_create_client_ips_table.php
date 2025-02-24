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
        Schema::create('client_ips', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id')->nullable()->comment('Partymax Client ID');
            $table->unsignedBigInteger('registration_number')->nullable();
            $table->string('device')->comment('Client Device type: Tablet, Smartphone, Desktop');
            $table->string('plataform')->comment('Client Device plataform: Linux, Windows, Mac');
            $table->string('browser')->comment('Client Device Browser: Chrome, Firefox, Opera');
            $table->boolean('is_bot')->comment('Client is a Bot: true or false');
            $table->string('ip')->comment('Client IP Address');
            $table->string('location');
            $table->string('postal_code');
            $table->string('coordinates');
            $table->string('timezone');
            $table->tinyInteger('is_blocked')->default(0);
            $table->timestamps();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_ips');
    }
};
