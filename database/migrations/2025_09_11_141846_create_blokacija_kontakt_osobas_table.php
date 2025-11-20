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
        Schema::create('blokacija_kontakt_osobas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blokacija_id')->constrained()->onDelete('cascade');
            $table->string('ime')->nullable();
            $table->string('telefon', 32)->nullable();
            $table->string('email')->nullable();
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
        Schema::dropIfExists('blokacija_kontakt_osobas');
    }
};
