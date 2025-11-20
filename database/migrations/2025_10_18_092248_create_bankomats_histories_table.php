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
        Schema::create('bankomats_histories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bankomat_id')->unsigned();
            $table->foreignId('bankomat_tip_id')->nullable()->constrained()->onDelete('set null');
            $table->string('b_sn', 32);
            $table->string('b_terminal_id', 32);
            $table->string('komentar', 255)->nullable();
            $table->timestamps();
            $table->foreignId('vlasnik_uredjaja')->nullable()->constrained('blokacijas')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bankomats_histories');
    }
};
