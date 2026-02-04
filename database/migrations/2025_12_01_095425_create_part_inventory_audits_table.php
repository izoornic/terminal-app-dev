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
        Schema::create('part_inventory_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lokacija_id')->constrained('lokacijas')->onDelete('cascade');
            $table->foreignId('korisnik_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['IN_PROGRESS', 'COMPLETED'])->default('IN_PROGRESS');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('completed_at')->nullable();
            $table->text('napomena')->nullable();
            
            $table->index(['lokacija_id', 'status']);
            $table->index(['korisnik_id', 'started_at']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('part_inventory_audits');
    }
};
