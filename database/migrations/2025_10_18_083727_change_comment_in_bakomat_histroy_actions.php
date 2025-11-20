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
        Schema::table('bankomat_lokacija_histroy_actions', function (Blueprint $table) {
            //
            $table->integer('vrsta_akcije')->change()->comment('1 - lokacija, 2 - tiketi, 3 - naplata');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bankomat_lokacija_histroy_actions', function (Blueprint $table) {
            //
        });
    }
};
