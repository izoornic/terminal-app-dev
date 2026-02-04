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
        Schema::create('part_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_type_id')->constrained('part_types')->onDelete('cascade');
            $table->foreignId('lokacija_id')->constrained('lokacijas')->onDelete('cascade');
            $table->integer('kolicina_ukupno')->default(0);
            $table->integer('kolicina_rezervisana')->default(0);
            $table->integer('kolicina_dostupna')->storedAs('kolicina_ukupno - kolicina_rezervisana');
            $table->timestamps();
            
            // Compound unique constraint - jedan deo može biti samo jednom po lokaciji
            $table->unique(['part_type_id', 'lokacija_id'], 'part_location_unique');
            
            // Indexes za brže pretraživanje
            $table->index(['lokacija_id', 'kolicina_dostupna']);
            $table->index(['part_type_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('part_stocks');
    }
};
