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
        Schema::table('bankomat_tiket_histories', function (Blueprint $table) {
            //
            $table->foreignId('action_id')->nullable()->constrained('bankomat_tiket_histroy_actions')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bankomat_tiket_histories', function (Blueprint $table) {
            //
            $table->dropColumn('bankomat_tiket_history_action_id');
        });
    }
};
