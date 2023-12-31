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
        Schema::create('selling_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('selling_id');
            $table->foreign('selling_id')->references('id')->on('selling');
            $table->unsignedBigInteger('stock_id');
            $table->foreign('stock_id')->references('id')->on('stock');
            $table->integer('price_kg');
            $table->integer('price_sell');
            $table->integer('qty');
            $table->integer('subtotal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('selling_detail');
    }
};
