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
        Schema::create('part_types', function (Blueprint $table) {
            $table->id();
            $table->string('sifra', 50)->unique();
            $table->string('naziv', 200);
            $table->text('opis')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('terminal_tips')->onDelete('set null');
            $table->decimal('cena', 10, 2)->default(0);
            $table->string('jedinica_mere', 20)->default('kom');
            $table->integer('min_kolicina')->default(0)->comment('Minimalna zaliha - threshold za upozorenje');
            $table->boolean('aktivan')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'aktivan']);
            $table->index(['sifra']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('part_types');
    }
};
