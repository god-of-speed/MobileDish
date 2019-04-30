<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDeliverersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deliverers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreign('user')->references('id')->on('users');
            $table->string('driverLicense')->nullable();
            $table->string('asset');
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
        Schema::dropIfExists('deliverers');
    }
}
