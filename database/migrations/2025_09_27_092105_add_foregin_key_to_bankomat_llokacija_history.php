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
        Schema::table('bankomat_locija_hirtories', function (Blueprint $table) {
            //
            $table->foreignId('history_action_id')->nullable()->constrained('bankomat_lokacija_histroy_actions')->onDelete('set null')->after('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bankomat_locija_hirtories', function (Blueprint $table) {
            //
        });
    }
};
