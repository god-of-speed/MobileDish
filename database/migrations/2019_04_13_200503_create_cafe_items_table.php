<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCafeItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cafe_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('cafe')->unsigned();
            $table->bigInteger('menu')->unsigned()->nullable();
            $table->bigInteger('category')->unsigned()->nullable();
            $table->foreign('cafe')->references('id')->on('cafes');
            $table->foreign('menu')->references('id')->on('cafe_menus');
            $table->foreign('category')->references('id')->on('cafe_categories');
            $table->string('name');
            $table->string('price');
            $table->string('oldPrice')->nullable();
            $table->string('discount')->nullable();
            $table->text('about')->nullable();
            $table->string('status')->default('set');
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
        Schema::dropIfExists('cafe_items');
    }
}
