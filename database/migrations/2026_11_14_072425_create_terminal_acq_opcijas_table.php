<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Opcije ACQ TID-a: šifarnik (stranica acq-opcije) i veza TID ↔ opcija.
 *
 * Opcija se ne briše nego deaktivira. Isključena opcija ostaje red u vezi sa
 * ukljucena = 0, pa veza pamti ko je i kada poslednji menjao opciju na TID-u.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('terminal_acq_opcijas', function (Blueprint $table) {
            $table->id();
            $table->string('naziv', 64)->unique();
            $table->string('opis', 255)->nullable();
            $table->unsignedInteger('redosled')->default(1);
            $table->boolean('aktivna')->default(true);
            $table->timestamps();
        });

        Schema::create('terminal_acq_opcija_tid', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terminal_acqId')->constrained('terminal_acq')->cascadeOnDelete();
            $table->foreignId('terminal_acq_opcijaId')->constrained('terminal_acq_opcijas')->cascadeOnDelete();
            $table->boolean('ukljucena')->default(true);
            $table->foreignId('korisnikId')->nullable()->constrained('users')->nullOnDelete()->comment('Korisnik poslednje izmene');
            $table->timestamps();
            $table->unique(['terminal_acqId', 'terminal_acq_opcijaId'], 'terminal_acq_opcija_tid_unique');
        });

        $sada = now();
        DB::table('terminal_acq_opcijas')->insert([
            ['naziv' => 'CNP predautorizacija', 'opis' => null, 'redosled' => 1, 'aktivna' => true, 'created_at' => $sada, 'updated_at' => $sada],
            ['naziv' => 'Cashback', 'opis' => null, 'redosled' => 2, 'aktivna' => true, 'created_at' => $sada, 'updated_at' => $sada],
            ['naziv' => 'Ručni unos', 'opis' => null, 'redosled' => 3, 'aktivna' => true, 'created_at' => $sada, 'updated_at' => $sada],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('terminal_acq_opcija_tid');
        Schema::dropIfExists('terminal_acq_opcijas');
    }
};
