<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bankomat_locija_hirtories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bankomat_lokacija_id')->unsigned();
            $table->bigInteger('bankomat_id')->unsigned();
            $table->bigInteger('blokacija_id')->unsigned();
            $table->bigInteger('bankomat_status_tip_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
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
        Schema::dropIfExists('bankomat_locija_hirtories');
    }
};
