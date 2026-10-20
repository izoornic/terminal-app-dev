<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Statusi koji postoje samo za TID (ts_za_uredjaj = 0) i ne nude se u dropdown-u
 * statusa uređaja. Postojeći statusi dobijaju ts_za_uredjaj = 1 (default).
 *
 * Redovi se ubacuju idempotentno po ts_naziv. Id-jevi kreću iznad 11: šifarnik
 * ima id-jeve 1-11 u svim okruženjima, a testovi ih upisuju sa fiksnim id-jem,
 * pa novi redovi u praznoj test bazi ne smeju da zauzmu 1-4.
 */
return new class extends Migration
{
    /**
     * @var list<string>
     */
    private const TID_STATUSI = [
        'Deinstaliran',
        'Prekonfigurisan',
        'Opozvan',
        'Čeka prekonfiguraciju',
    ];

    private const POSLEDNJI_POSTOJECI_ID = 11;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('terminal_status_tips', function (Blueprint $table) {
            $table->boolean('ts_za_uredjaj')->default(true)->after('ts_naziv');
        });

        $sledeciId = max((int) DB::table('terminal_status_tips')->max('id'), self::POSLEDNJI_POSTOJECI_ID) + 1;

        foreach (self::TID_STATUSI as $naziv) {
            if (DB::table('terminal_status_tips')->where('ts_naziv', $naziv)->exists()) {
                DB::table('terminal_status_tips')->where('ts_naziv', $naziv)->update(['ts_za_uredjaj' => false]);

                continue;
            }

            DB::table('terminal_status_tips')->insert([
                'id' => $sledeciId++,
                'ts_naziv' => $naziv,
                'ts_za_uredjaj' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('terminal_status_tips')->whereIn('ts_naziv', self::TID_STATUSI)->delete();

        Schema::table('terminal_status_tips', function (Blueprint $table) {
            $table->dropColumn('ts_za_uredjaj');
        });
    }
};
