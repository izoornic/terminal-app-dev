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
        Schema::create('bankomats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bankomat_tip_id')->nullable()->constrained()->onDelete('set null');
            $table->string('b_sn', 32)->unique();
            $table->string('b_terminal_id', 32);
            $table->string('komentar', 255)->nullable();
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
        Schema::dropIfExists('bankomats');
    }
};
