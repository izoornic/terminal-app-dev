<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Snapshot terminal_acq reda pre svake izmene (obrazac terminal_lokacija_histories).
 *
 * terminal_statusId, lokacijaId i acq_mid su EFEKTIVNE vrednosti u tom trenutku
 * (i one nasleđene od uređaja / lokacije), da istorija ostane čitljiva kad se
 * uređaj kasnije premesti ili kad prekonfiguracija zameni MID lokacije.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('terminal_acq_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('terminal_acqId')->index();
            $table->string('acq_tid', 16)->index();
            $table->string('naziv_na_slipu', 64)->nullable();
            $table->string('komunikacija', 16)->nullable();
            $table->string('iccid', 22)->nullable();
            $table->string('inventarni_broj', 24)->nullable();
            $table->text('dodatne_opcije')->nullable();
            $table->boolean('testni')->default(false);
            $table->text('napomena')->nullable();
            $table->date('datum_zahteva')->nullable();
            $table->date('datum_instalacije')->nullable();
            $table->integer('terminalId')->nullable()->index();
            $table->integer('terminal_statusId')->nullable();
            $table->integer('lokacijaId')->nullable();
            $table->string('acq_mid', 20)->nullable();
            $table->integer('prethodni_terminal_acqId')->nullable();
            $table->string('zahtev_acq_mid', 20)->nullable();
            $table->integer('trazeni_terminal_tipId')->nullable();
            $table->string('izvor', 16);
            $table->string('izvor_fajl', 255)->nullable();
            $table->integer('korisnikId');
            $table->string('korisnikIme', 128);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminal_acq_histories');
    }
};
