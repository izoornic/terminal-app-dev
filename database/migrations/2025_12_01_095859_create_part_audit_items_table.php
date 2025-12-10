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
        Schema::create('part_audit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('audit_id')->constrained('part_inventory_audits')->onDelete('cascade');
            $table->foreignId('part_type_id')->constrained('part_types')->onDelete('cascade');
            $table->integer('expected_kolicina')->comment('Očekivana količina iz sistema');
            $table->integer('actual_kolicina')->comment('Stvarna prebrojana količina');
            $table->integer('razlika')->storedAs('actual_kolicina - expected_kolicina');
            $table->text('napomena')->nullable();
            
            $table->index(['audit_id']);
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
        Schema::dropIfExists('part_audit_items');
    }
};
