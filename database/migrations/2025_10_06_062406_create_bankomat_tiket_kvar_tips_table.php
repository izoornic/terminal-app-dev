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
        Schema::create('bankomat_tiket_kvar_tips', function (Blueprint $table) {
            $table->id();
            $table->integer('list_id');
            $table->foreignId('bakomat_product_tip_id')->nullable()->constrained('bankomat_product_tips')->onDelete('set null');
            $table->string('btkt_naziv', 64);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bankomat_tiket_kvar_tips');
    }
};
