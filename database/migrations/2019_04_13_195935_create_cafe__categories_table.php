<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCafeCategoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cafe_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreign('cafe')->references('id')->on('cafes');
            $table->foreign('menu')->references('id')->on('cafe_menus')->nullable();
            $table->string('name');
            $table->text('about')->nullable();
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
        Schema::dropIfExists('cafe__categories');
    }
}
