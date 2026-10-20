<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Podaci banke o trgovcu (HALK): MID, MCC, PTT i precizna lokacija.
 *
 * acq_mid nije unique - isti MID mogu imati glavna lokacija i podlokacija.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('lokacijas', function (Blueprint $table) {
            $table->string('acq_mid', 20)->nullable()->after('pib');
            $table->char('mcc', 4)->nullable()->after('acq_mid');
            $table->string('ptt', 5)->nullable()->after('mesto');
            $table->string('precizna_lokacija', 128)->nullable()->after('adresa');

            $table->index('acq_mid');
            $table->index('pib');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lokacijas', function (Blueprint $table) {
            $table->dropIndex(['acq_mid']);
            $table->dropIndex(['pib']);
            $table->dropColumn(['acq_mid', 'mcc', 'ptt', 'precizna_lokacija']);
        });
    }
};
