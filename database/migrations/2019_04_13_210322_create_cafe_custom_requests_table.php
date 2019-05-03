<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCafeCustomRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cafe_custom_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreign('cafe')->references('id')->on('cafes');
            $table->foriegn('user')->references('id')->on('users');
            $table->text('customRequest');
            $table->string('price');
            $table->string('duration');
            $table->string('discount')->default(0);
            $table->string('userStatus')->default('start');
            $table->string('cafeStatus')->default('start');
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
        Schema::dropIfExists('cafe_custom_requests');
    }
}
