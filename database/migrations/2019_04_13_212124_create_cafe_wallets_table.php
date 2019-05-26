<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCafeWalletsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cafe_wallets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('cafe')->unsigned();
            $table->bigInteger('user1')->unsigned();
            $table->bigInteger('user2')->unsigned()->nullable();
            $table->foreign('cafe')->references('id')->on('cafes');
            $table->string('availableBal');
            $table->string('previousBal');
            $table->string('virtualMoney');
            $table->foreign('user1')->references('id')->on('users')->nullable();
            $table->foreign('user2')->references('id')->on('users')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('cafe_wallets');
    }
}
