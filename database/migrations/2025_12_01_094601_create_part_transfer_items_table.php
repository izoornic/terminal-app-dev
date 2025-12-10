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
        Schema::create('part_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transfer_id')->constrained('part_transfers')->onDelete('cascade');
            $table->foreignId('part_type_id')->constrained('part_types')->onDelete('cascade');
            $table->integer('kolicina');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index(['transfer_id']);
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
        Schema::dropIfExists('part_transfer_items');
    }
};
