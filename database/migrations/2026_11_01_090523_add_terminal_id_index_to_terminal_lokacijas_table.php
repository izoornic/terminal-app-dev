<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Indeks na terminal_lokacijas.terminalId: lista ACQ terminala (i efektivni status/lokacija
 * TID-a) spaja terminal_acq sa terminal_lokacijas po terminalId. Bez indeksa MariaDB na
 * serveru za svaki TID čita celu tabelu (~16.500 redova), pa stranica posle uvoza radi sporo.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasIndex('terminal_lokacijas', ['terminalId'])) {
            return;
        }

        Schema::table('terminal_lokacijas', function (Blueprint $table) {
            $table->index('terminalId');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasIndex('terminal_lokacijas', 'terminal_lokacijas_terminalid_index')) {
            return;
        }

        Schema::table('terminal_lokacijas', function (Blueprint $table) {
            $table->dropIndex(['terminalId']);
        });
    }
};
