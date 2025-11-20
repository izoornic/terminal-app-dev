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
        Schema::table('pozicija_tips', function (Blueprint $table) {
            //
            $table->string('dashboard_path')->nullable()->after('opis')->comment('putanja do dashboarda za tu poziciju');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pozicija_tips', function (Blueprint $table) {
            //
        });
    }
};
