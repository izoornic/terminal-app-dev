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
        Schema::create('lokacijas', function (Blueprint $table) {
            $table->id();
            $table->integer('regionId')->default(1);
            $table->integer('lokacija_tipId')->default(1);
            $table->string('l_naziv');
            $table->string('mesto');
            $table->string('adresa');
            $table->decimal('latitude', 9,6)->nullable();
            $table->decimal('longitude', 9,6)->nullable();
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
        Schema::dropIfExists('lokacijas');
    }
};
