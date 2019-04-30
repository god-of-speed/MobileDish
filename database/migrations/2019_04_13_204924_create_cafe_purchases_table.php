<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCafePurchasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cafe_purchases', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreign('cafe')->references('id')->on('cafes');
            $table->foreign('item')->references('id')->on('cafe_items');
            $table->foreign('user')->references('id')->on('user');
            $table->string('status')->default('pending');
            $table->string('deliveryType');
            $table->foreign('deliverer')->references('id')->on('deliverers')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cafe_purchases');
    }
}
