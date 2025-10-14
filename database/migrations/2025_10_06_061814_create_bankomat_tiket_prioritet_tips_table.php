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
        Schema::create('bankomat_tiket_prioritet_tips', function (Blueprint $table) {
            $table->id();
            $table->string('btpt_naziv', 64);
            $table->string('btpt_opis', 256);
            $table->string('btn_collor', 32);
            $table->string('btn_hover_collor', 32);
            $table->string('tr_bg_collor', 32);
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
        Schema::dropIfExists('bankomat_tiket_prioritet_tips');
    }
};
