<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCafeMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cafe_menus', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreign('cafe')->references('id')->on('cafes');
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
        Schema::dropIfExists('cafe_menus');
    }
}
