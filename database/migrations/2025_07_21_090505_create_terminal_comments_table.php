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
        Schema::create('terminal_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terminal_lokacijaId')->constrained('terminal_lokacijas')->onDelete('cascade')->comment('ID terminal lokacije');
            $table->foreignId('userId')->constrained('users')->onDelete('cascade')->comment('ID korisnika koji je ostavio komentar');
            $table->boolean('is_active')->default(true);
            $table->text('comment');
            $table->softDeletes();
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
        Schema::dropIfExists('terminal_comments');
    }
};
