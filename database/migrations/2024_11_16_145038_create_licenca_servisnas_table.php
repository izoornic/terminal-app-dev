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
        Schema::create('licenca_servisnas', function (Blueprint $table) {
            $table->id();
            $table->integer('userId');
            $table->integer('terminal_lokacijaId');
            $table->integer('distributerId');
            $table->integer('licenca_distributer_cenaId');
            $table->date('datum_pocetka_licence')->nullable();
            $table->date('datum_kraj_licence')->nullable();
            $table->date('datum_isteka_prekoracenja')->nullable();
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
        Schema::dropIfExists('licenca_servisnas');
    }
};