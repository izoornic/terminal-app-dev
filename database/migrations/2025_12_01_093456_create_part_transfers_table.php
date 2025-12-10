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
        Schema::create('part_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_broj', 50)->unique();
            $table->foreignId('source_lokacija_id')->constrained('lokacijas')->onDelete('cascade');
            $table->foreignId('destination_lokacija_id')->constrained('lokacijas')->onDelete('cascade');
            $table->enum('status', ['PENDING', 'COMPLETED', 'CANCELLED'])->default('PENDING');
            $table->foreignId('kreirao_korisnik_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('odobrio_korisnik_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('napomena')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index(['transfer_broj']);
            $table->index(['source_lokacija_id', 'status']);
            $table->index(['destination_lokacija_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('part_transfers');
    }
};
