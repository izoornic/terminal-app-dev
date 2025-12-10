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
        Schema::create('part_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_type_id')->constrained('part_types')->onDelete('cascade');
            $table->foreignId('lokacija_id')->constrained('lokacijas')->onDelete('cascade');
            $table->enum('tip_kretanja', [
                'ULAZ', 
                'IZLAZ', 
                'TRANSFER_OUT', 
                'TRANSFER_IN', 
                'REZERVACIJA', 
                'POVRAT'
            ]);
            $table->integer('kolicina');
            $table->foreignId('povezana_lokacija_id')->nullable()->constrained('lokacijas')->onDelete('set null');
            $table->foreignId('korisnik_id')->constrained('users')->onDelete('cascade');
            $table->string('dokument', 100)->nullable()->comment('Broj dokumenta, fakture, ili ID transfera');
            $table->text('napomena')->nullable();
            $table->timestamp('created_at')->useCurrent();
            
            // Indexes
            $table->index(['lokacija_id', 'created_at']);
            $table->index(['part_type_id', 'created_at']);
            $table->index(['tip_kretanja', 'created_at']);
            $table->index(['dokument']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('part_movements');
    }
};
