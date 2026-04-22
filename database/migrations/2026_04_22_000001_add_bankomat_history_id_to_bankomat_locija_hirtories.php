<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bankomat_locija_hirtories', function (Blueprint $table) {
            $table->foreignId('bankomat_history_id')->nullable()->constrained('bankomats_histories')->nullOnDelete()->after('bankomat_tiket_id');
        });
    }

    public function down(): void
    {
        Schema::table('bankomat_locija_hirtories', function (Blueprint $table) {
            $table->dropForeign(['bankomat_history_id']);
            $table->dropColumn('bankomat_history_id');
        });
    }
};
