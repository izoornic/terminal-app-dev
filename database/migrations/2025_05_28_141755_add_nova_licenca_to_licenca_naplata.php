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
            $table->dropColumn('licenca_dist_terminalId');
            $table->integer('nova_licenca')->after('licenca_distributer_cenaId')->default(0);
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
