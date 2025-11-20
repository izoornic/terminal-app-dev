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
        Schema::table('bankomat_tips', function (Blueprint $table) {
            //
            $table->foreignId('bankomat_produkt_tip_id')->nullable()->constrained('bankomat_product_tips')->onDelete('set null')->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bankomat_tips', function (Blueprint $table) {
            //
        });
    }
};
