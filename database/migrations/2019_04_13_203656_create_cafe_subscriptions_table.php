<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCafeSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cafe_subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('cafe')->unsigned();
            $table->bigInteger('user')->unsigned();
            $table->foreign('cafe')->references('id')->on('cafes');
            $table->foreign('user')->references('id')->on('users');
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
        Schema::dropIfExists('cafe_subscriptions');
    }
}
