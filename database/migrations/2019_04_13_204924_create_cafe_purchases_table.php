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
            $table->string('userStatus')->default('start');
            $table->string('cafeStatus')->default('start');
            $table->integer('quantity')->default(0);
            $table->string('comment')->nullable();
            $table->string('country');
            $table->string('state');
            $table->string('location');
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
