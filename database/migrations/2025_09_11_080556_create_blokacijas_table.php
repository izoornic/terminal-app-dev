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
        Schema::create('blokacijas', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_duplicate')->default(false);
            $table->foreignId('bankomat_region_id')->nullable()->constrained('bankomat_regions')->onDelete('set null');
            $table->foreignId('blokacija_tip_id')->nullable()->constrained('blokacija_tips')->onDelete('set null');
            $table->string('bl_naziv');
            $table->string('bl_naziv_sufix', 125)->nullable();
            $table->string('bl_adresa')->nullable();
            $table->string('bl_mesto')->nullable();
            $table->string('pib', 16)->nullable();
            $table->string('mb', 16)->nullable();
            $table->string('email')->nullable();
            $table->decimal('latitude', 11, 9)->nullable();
            $table->decimal('longitude', 11, 9)->nullable();
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
        Schema::dropIfExists('blokacijas');
    }
};
