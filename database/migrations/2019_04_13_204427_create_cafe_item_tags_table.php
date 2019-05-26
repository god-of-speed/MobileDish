<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCafeItemTagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cafe_item_tags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('item')->unsigned();
            $table->bigInteger('tag')->unsigned();
            $table->foreign('item')->references('id')->on('cafe_items');
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
        Schema::dropIfExists('cafe_item_tags');
    }
}
