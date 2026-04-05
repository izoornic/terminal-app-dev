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
        Schema::table('bankomat_tikets', function (Blueprint $table) {
            //
            $table->boolean('naplata')->default(0)->after('bankomat_tiket_prioritet_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bankomat_tikets', function (Blueprint $table) {
            //
        });
    }
};
