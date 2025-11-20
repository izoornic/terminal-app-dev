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
            $table->foreignId('kat_id')->nullable()->constrained('pozicija_kategoriys')->after('id')->onDelete('set null');
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
            $table->dropForeign('kat_id');
        });
    }
};
