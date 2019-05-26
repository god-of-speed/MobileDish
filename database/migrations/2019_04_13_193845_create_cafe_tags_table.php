<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCafeTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cafe_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('cafe')->unsigned();
            $table->bigInteger('tag')->unsigned();
            $table->foreign('cafe')->references('id')->on('cafes');
            $table->foreign('tag')->references('id')->on('tags');
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
        Schema::dropIfExists('cafe_tags');
    }
}
