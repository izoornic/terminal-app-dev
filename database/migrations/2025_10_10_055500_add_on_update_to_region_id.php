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
        Schema::table('blokacijas', function (Blueprint $table) {
            //
            //$table->foreignId('bankomat_region_id')->change()->nullable()->cascadeOnUpdate();
            /* $table->dropForeign('blokacijas_bankomat_region_id_foreign');
            $table->dropForeign('blokacijas_blokacija_tip_id_foreign'); */
            $table->foreignId('bankomat_region_id')->change()->nullable()->constrained('bankomat_regions')->onUpdate('cascade')->onDelete('set null');
            $table->foreignId('blokacija_tip_id')->change()->nullable()->constrained('blokacija_tips')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('blokacijas', function (Blueprint $table) {
            //
            $table->dropForeign('blokacijas_bankomat_region_id_foreign');
        });
    }
};
