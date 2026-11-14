<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Komentari ACQ TID-a (kao terminal_comments za uređaj): prate TID kroz ceo životni vek,
 * i dok čeka uređaj i kad je zatvoren. Brisanje je meko (is_active = 0, deleted_at).
 * Na terminal_acq brojač i poslednji komentar, kao na terminal_lokacijas.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('terminal_acq_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('terminal_acqId')->constrained('terminal_acq')->cascadeOnDelete()->comment('ID ACQ TID-a');
            $table->foreignId('userId')->constrained('users')->cascadeOnDelete()->comment('ID korisnika koji je ostavio komentar');
            $table->boolean('is_active')->default(true);
            $table->text('comment');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('terminal_acq', function (Blueprint $table) {
            $table->integer('br_komentara')->default(0)->after('korisnikIme')->comment('Broj aktivnih komentara TID-a');
            $table->foreignId('last_comment_userId')->nullable()->after('br_komentara')->constrained('users')->nullOnDelete()->comment('Korisnik poslednje izmene komentara');
            $table->dateTime('last_comment_at')->nullable()->after('last_comment_userId')->comment('Vreme poslednje izmene komentara');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('terminal_acq', function (Blueprint $table) {
            $table->dropConstrainedForeignId('last_comment_userId');
            $table->dropColumn(['br_komentara', 'last_comment_at']);
        });

        Schema::dropIfExists('terminal_acq_comments');
    }
};
