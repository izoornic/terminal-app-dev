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
        Schema::table('lokacijas', function (Blueprint $table) {
            //
            $table->boolean('is_duplicate')->default(false)->after('id');
            $table->string('l_naziv_sufix')->nullable()->after('l_naziv');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lokacijas', function (Blueprint $table) {
            //
        });
    }
};
