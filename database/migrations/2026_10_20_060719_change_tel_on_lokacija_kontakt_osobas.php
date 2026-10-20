<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * tel 16 -> 32: u HALK podacima su često dva broja u jednom polju (do 27 znakova).
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lokacija_kontakt_osobas', function (Blueprint $table) {
            $table->string('tel', 32)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lokacija_kontakt_osobas', function (Blueprint $table) {
            $table->string('tel', 16)->nullable()->change();
        });
    }
};
