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
        Schema::table('licenca_servisnas', function (Blueprint $table) {
            //
            $table->string('licenca_naziv', 32)->after('terminal_lokacijaId');
            $table->string('terminal_sn', 32)->after('terminal_lokacijaId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('licenca_servisnas', function (Blueprint $table) {
            //
        });
    }
};
