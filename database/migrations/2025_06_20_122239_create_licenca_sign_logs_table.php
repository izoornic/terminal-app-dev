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
        Schema::create('licenca_sign_logs', function (Blueprint $table) {
            $table->id();
            $table->string('terminal_sn', 20)->comment('SN of the terminal');
            $table->string('signature', 512)->comment('Signature of the licence');
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
        Schema::dropIfExists('licenca_sign_logs');
    }
};
