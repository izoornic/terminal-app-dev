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
        Schema::create('bankomat_tiket_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bankomat_tiket_id')->constrained('bankomat_tikets')->onDelete('cascade');
            $table->foreignId('bankomat_lokacija_id')->nullable()->constrained('bankomat_lokacijas')->onUpdate('cascade')->onDelete('set null');
            $table->enum('status', ['Otvoren', 'Dodaljen', 'Zatvoren'])->default('Otvoren');
            $table->foreignId('bankomat_tiket_kvar_tip_id')->nullable()->constrained('bankomat_tiket_kvar_tips')->onDelete('set null');
            $table->foreignId('bankomat_tiket_prioritet_id')->nullable()->constrained('bankomat_tiket_prioritet_tips')->onDelete('set null');
            $table->text('opis');
            $table->foreignId('user_prijava_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('user_dodeljen_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('user_zatvorio_id')->nullable()->constrained('users')->onDelete('set null');
            $table->integer('br_komentara')->default(0);
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
        Schema::dropIfExists('bankomat_tiket_histories');
    }
};
