<?php

namespace App\Console\Commands;

use App\Actions\TerminalAcq\HalkCsvIzvestaj;
use App\Actions\TerminalAcq\HalkExcelCitac;
use App\Actions\TerminalAcq\HalkUvoz as HalkUvozAction;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

/**
 * Faza 3 HALK evidencije: jednokratan uvoz glavne tabele u terminal_acq.
 *
 * Bez --potvrdi uvoz se izvrši u transakciji koja se na kraju poništi: izveštaj je
 * isti kao kod pravog uvoza, a baza ostaje nepromenjena.
 *
 * Probno:  php artisan halk:uvoz "beleske/HALK instalirani.xlsx" --izlaz=beleske
 * Upis:    php artisan halk:uvoz "beleske/HALK instalirani.xlsx" --potvrdi --korisnik=1
 */
class HalkUvoz extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'halk:uvoz
        {fajl : Putanja do HALK Excel tabele}
        {--sheet=HALK : Naziv sheet-a}
        {--izlaz= : Folder za CSV izveštaj (podrazumevano storage/app/halk)}
        {--potvrdi : Upiši uvoz; bez ove opcije uvoz je probni i ništa ne ostaje u bazi}
        {--korisnik= : id korisnika koji se upisuje kao autor (obavezno uz --potvrdi)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'HALK: jednokratan uvoz glavne Excel tabele u terminal_acq (probno bez --potvrdi)';

    /**
     * Execute the console command.
     */
    public function handle(HalkExcelCitac $citac, HalkUvozAction $uvoz): int
    {
        $potvrdi = (bool) $this->option('potvrdi');
        $korisnik = User::find($this->option('korisnik'));
        if ($potvrdi && $korisnik === null) {
            $this->error('Uz --potvrdi je obavezan --korisnik=<id postojećeg korisnika>.');

            return self::FAILURE;
        }
        $korisnik ??= (new User)->forceFill(['id' => 0, 'name' => 'Probni uvoz']);

        $fajl = $this->argument('fajl');
        if (! is_file($fajl)) {
            $this->error("Fajl ne postoji: {$fajl}");

            return self::FAILURE;
        }

        try {
            $redovi = $citac->procitaj($fajl, ['acq_tid', 'sn', 'pib', 'status'], $this->option('sheet'));
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        DB::beginTransaction();
        try {
            $izvestaj = $uvoz->uvezi($redovi, $korisnik, basename($fajl));
        } catch (Throwable $e) {
            DB::rollBack();
            $this->error($e->getMessage());

            return self::FAILURE;
        }
        $potvrdi ? DB::commit() : DB::rollBack();

        $putanja = HalkCsvIzvestaj::upisi(
            $this->option('izlaz'),
            $potvrdi ? 'halk_uvoz' : 'halk_uvoz_probni',
            ['Red u Excelu', 'TID', 'SN', 'Rezultat', 'Opis'],
            array_map(fn (array $stavka): array => [$stavka['red'], $stavka['tid'], $stavka['sn'], $stavka['rezultat'], $stavka['opis']], $izvestaj)
        );

        $this->info('Pročitano redova: '.count($redovi));
        $this->table(['Rezultat', 'Stavki'], $this->rezime($izvestaj));
        $this->info("Izveštaj: {$putanja}");
        $potvrdi
            ? $this->info("Uvoz upisan (autor: {$korisnik->name}).")
            : $this->warn('Probni uvoz: ništa nije upisano. Posle pregleda izveštaja pokreni ponovo sa --potvrdi --korisnik=<id>.');

        return self::SUCCESS;
    }

    /**
     * @param  list<array<string, mixed>>  $izvestaj
     * @return list<array{0: string, 1: int}>
     */
    private function rezime(array $izvestaj): array
    {
        $poRezultatu = array_count_values(array_column($izvestaj, 'rezultat'));
        $redovi = [];
        foreach ([HalkUvozAction::AKTIVAN, HalkUvozAction::ZATVOREN, HalkUvozAction::STARI_TID, HalkUvozAction::ISTORIJA, HalkUvozAction::POSTOJI, HalkUvozAction::PRESKOCEN] as $rezultat) {
            $redovi[] = [$rezultat, $poRezultatu[$rezultat] ?? 0];
        }

        return $redovi;
    }
}
