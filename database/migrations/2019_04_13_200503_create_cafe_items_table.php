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
            $table->foreign('cafe')->references('id')->on('cafes');
            $table->foreign('menu')->references('id')->on('cafe_menus')->nullable();
            $table->foreign('category')->references('id')->on('cafe_categories')->nullable();
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
