<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * HALK evidencija po ACQ TID-u: jedan red po TID-u, trenutno stanje.
 *
 * Kao kod terminal_lokacijas: integer camelCase ključevi bez FK constraint-a.
 * Dok je terminalId popunjen, terminal_statusId i lokacijaId su null - status i
 * lokacija se tada čitaju iz terminal_lokacijas (jedan izvor istine).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('terminal_acq', function (Blueprint $table) {
            $table->id();
            $table->string('acq_tid', 16)->unique();
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
            $table->integer('terminal_statusId')->nullable()->index();
            $table->integer('lokacijaId')->nullable()->index();
            $table->integer('prethodni_terminal_acqId')->nullable()->index();
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
        Schema::dropIfExists('terminal_acq');
    }
};
