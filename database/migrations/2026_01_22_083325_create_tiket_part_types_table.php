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
        Schema::create('tiket_part_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tiket_id')->constrained('tikets')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('part_type_id')->constrained('part_types')->onUpdate('cascade')->onDelete('cascade');
            $table->foreignId('part_location_id')->constrained('lokacijas')->onUpdate('cascade')->onDelete('cascade');
            $table->bigInteger('user_id')->nullable();
            $table->foreignId('terminal_lokacija_id')->nullable()->constrained('terminal_lokacijas')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('quantity')->default(1);
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
        Schema::dropIfExists('tiket_part_types');
    }
};
