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
        Schema::table('terminal_lokacijas', function (Blueprint $table) {
            //
            $table->integer('br_komentara')->default(0)->after('korisnikIme')->comment('Broj komentara za terminal');
            $table->foreignId('last_comment_userId')->nullable()->after('br_komentara')->constrained('users')->onDelete('set null')->comment('ID korisnika koji je ostavio posljednji komentar');
            $table->dateTime('last_comment_at')->nullable()->after('last_comment_userId')->comment('Vrijeme kada je ostavljen posljednji komentar');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('terminal_lokacijas', function (Blueprint $table) {
            //
        });
    }
};
