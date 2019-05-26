<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCafeMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cafe_members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('cafe')->unsigned();
            $table->bigInteger('user')->unsigned();
            $table->foreign('cafe')->references('id')->on('cafes');
            $table->foreign('user')->references('id')->on('users');
            $table->string('right')->default('member');
            $table->string('status')->default('pending');
            $table->string('requestType')->nullable();
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
        Schema::dropIfExists('cafe_members');
    }
}
