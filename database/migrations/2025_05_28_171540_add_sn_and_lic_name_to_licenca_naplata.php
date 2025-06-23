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
        Schema::table('licenca_naplatas', function (Blueprint $table) {
            //
            $table->dropColumn('broj_dana');
            $table->string('terminal_sn',32)->nullable()->after('licenca_distributer_cenaId');
            $table->string('licenca_naziv', 32)->nullable()->after('licenca_distributer_cenaId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('licenca_naplatas', function (Blueprint $table) {
            //
        });
    }
};
