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
            $table->foreign('cafe')->references('id')->on('cafes');
            $table->foreign('user')->references('id')->on('users');
            $table->string('right')->default('member');
            $table->string('status')->default('pending');
            $table->string('requestType');
            $table->string('payment')->nullable();
            $table->string('interval')->nullable();
            $table->datetime('startPayDate')->nullable();
            $table->datetime('lastPayDate')->nullable();
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
        Schema::dropIfExists('cafe__members');
    }
}
