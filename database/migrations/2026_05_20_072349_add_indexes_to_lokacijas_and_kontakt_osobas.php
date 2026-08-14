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
    public function up(): void
    {
        Schema::table('lokacija_kontakt_osobas', function (Blueprint $table) {
            $table->index('lokacijaId');
        });

        Schema::table('lokacijas', function (Blueprint $table) {
            $table->index('regionId');
            $table->index('lokacija_tipId');
        });
    }

    public function down(): void
    {
        Schema::table('lokacija_kontakt_osobas', function (Blueprint $table) {
            $table->dropIndex(['lokacijaId']);
        });

        Schema::table('lokacijas', function (Blueprint $table) {
            $table->dropIndex(['regionId']);
            $table->dropIndex(['lokacija_tipId']);
        });
    }
};
