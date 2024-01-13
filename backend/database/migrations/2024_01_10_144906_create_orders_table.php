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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('shipping_state_id');
            $table->unsignedBigInteger('payment_state_id');
            $table->unsignedBigInteger('address_id');
            $table->date('date')->default(now());
            $table->string('sub_total');
            $table->string('shipping_total');
            $table->string('tax');
            $table->string('total');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('shipping_state_id')->references('id')->on('shipping_states')->onDelete('cascade');
            $table->foreign('payment_state_id')->references('id')->on('payment_states')->onDelete('cascade');
            $table->foreign('address_id')->references('id')->on('addresses')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
