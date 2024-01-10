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
        Schema::create('billings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('province_id');
            $table->tinyInteger('default')->default(0);
            $table->tinyInteger('pse')->default(0);
            $table->string('card_number')->nullable();
            $table->string('name')->nullable();
            $table->string('expired_date')->nullable();
            $table->string('cvv_code')->nullable();
            $table->string('phone')->nullable();
            $table->longtext('address')->nullable();
            $table->text('street')->nullable();
            $table->string('city')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->timestamps();

            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->foreign('province_id')->references('id')->on('provinces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billings');
    }
};
