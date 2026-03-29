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
        Schema::table('terminal_lokacijas', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('terminal_campagin_id')->nullable()->after('terminal_statusId');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('terminal_lokacijas', function (Blueprint $table) {
            //
            $table->dropColumn('terminal_campagin_id');
        });
    }
};
