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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('iso')->nullable();
            $table->string('name');
            $table->string('timezone');
            $table->string('nicename')->nullable();
            $table->string('iso3')->nullable();
            $table->integer('numcode')->nullable();
            $table->string('phonecode')->nullable();
            $table->unsignedSmallInteger('phone_digits')->default(0)->comment('Maximum number of phone digits');
            $table->string('flag')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
