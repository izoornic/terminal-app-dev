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
        Schema::create('part_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_type_id')->constrained('part_types')->onDelete('cascade');
            $table->foreignId('lokacija_id')->constrained('lokacijas')->onDelete('cascade');
            $table->integer('kolicina');
            $table->foreignId('korisnik_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['AKTIVNA', 'ISKORISCENA', 'OTKAZANA'])->default('AKTIVNA');
            $table->unsignedBigInteger('radni_nalog_id')->nullable()->comment('FK na work_orders ako postoji');
            $table->timestamp('rezervisano_do')->nullable();
            $table->timestamps();
            
            $table->index(['lokacija_id', 'status']);
            $table->index(['part_type_id', 'status']);
            $table->index(['radni_nalog_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('part_reservations');
    }
};
