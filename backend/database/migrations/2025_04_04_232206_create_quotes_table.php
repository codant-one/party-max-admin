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
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('document_type_id');
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('document');
            $table->string('phone');
            $table->string('email');
            $table->date('start_date');
            $table->date('due_date');
            $table->string('sub_total');
            $table->string('tax');
            $table->string('total');
            $table->tinyInteger('type')->default(0);
            $table->string('file')->nullable();
            $table->timestamps();

            $table->foreign('document_type_id')->references('id')->on('document_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
