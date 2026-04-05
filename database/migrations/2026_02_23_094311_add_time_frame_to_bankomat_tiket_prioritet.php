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
        Schema::table('bankomat_tiket_prioritet_tips', function (Blueprint $table) {
            //
            $table->time('time_frame')->nullable()->after('btpt_naziv');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bankomat_tiket_prioritet_tips', function (Blueprint $table) {
            //
            $table->dropColumn('time_frame');
        });
    }
};
