<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFavouritesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('favourite_cafes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('user')->unsigned();
            $table->bigInteger('cafe')->unsigned();
            $table->foreign('user')->references('id')->on('users');
            $table->foreign('cafe')->references('id')->on('cafes');
            $table->integer('value')->default(0);
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
        Schema::dropIfExists('favourites');
    }
}
