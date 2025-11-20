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
        Schema::create('bankomat_lokacija_histroy_actions', function (Blueprint $table) {
            $table->id();
            $table->integer('vrsta_akcije')->comment('1 - lokacija, 2 - tiketi');
            $table->string('akcija', 64);
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
        Schema::dropIfExists('bankomat_lokacija_histroy_actions');
    }
};
