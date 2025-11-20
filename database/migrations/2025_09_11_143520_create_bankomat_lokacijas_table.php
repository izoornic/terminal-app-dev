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
        Schema::create('bankomat_lokacijas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bankomat_id')->constrained()->onDelete('cascade');
            $table->foreignId('blokacija_id')->constrained()->onDelete('cascade');
            $table->foreignId('bankomat_status_tip_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
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
        Schema::dropIfExists('bankomat_lokacijas');
    }
};
