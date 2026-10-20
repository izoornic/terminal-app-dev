<?php

namespace App\Console\Commands;

use App\Actions\TerminalAcq\HalkCsvIzvestaj;
use App\Actions\TerminalAcq\HalkExcelCitac;
use App\Actions\TerminalAcq\HalkIspravkaStatusa;
use App\Models\User;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Uređaji koje banka vodi kao instalirane, a u aplikaciji su kod korisnika sa statusom
 * "Za instalaciju": status → "Instaliran", uz red u terminal_lokacija_histories.
 *
 * Bez --potvrdi samo pravi listu (CSV) i ništa ne menja.
 *
 * Lista:   php artisan halk:ispravi-status "beleske/HALK instalirani CELA TABELA.xlsx" --izlaz=beleske
 * Upis:    php artisan halk:ispravi-status "beleske/HALK instalirani CELA TABELA.xlsx" --potvrdi --korisnik=1
 */
class HalkIspraviStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'halk:ispravi-status
        {fajl : Putanja do HALK Excel tabele}
        {--sheet=HALK : Naziv sheet-a}
        {--izlaz= : Folder za CSV listu (podrazumevano storage/app/halk)}
        {--potvrdi : Upiši izmene; bez ove opcije komanda samo pravi listu}
        {--korisnik= : id korisnika koji se upisuje kao autor izmene (obavezno uz --potvrdi)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'HALK: status "Za instalaciju" → "Instaliran" za uređaje koje banka vodi kao instalirane';

    /**
     * Execute the console command.
     */
    public function handle(HalkExcelCitac $citac, HalkIspravkaStatusa $ispravka): int
    {
        $korisnik = null;
        if ($this->option('potvrdi')) {
            $korisnik = User::find($this->option('korisnik'));
            if ($korisnik === null) {
                $this->error('Uz --potvrdi je obavezan --korisnik=<id postojećeg korisnika>.');

                return self::FAILURE;
            }
        }

        $fajl = $this->argument('fajl');
        if (! is_file($fajl)) {
            $this->error("Fajl ne postoji: {$fajl}");

            return self::FAILURE;
        }

        try {
            $redovi = $citac->procitaj($fajl, ['acq_tid', 'sn', 'pib'], $this->option('sheet'));
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $kandidati = $ispravka->kandidati($redovi);
        $putanja = $this->upisiCsv($kandidati);

        $this->table(['Rezultat', 'Uređaja'], [
            ['Za ispravku', count($kandidati['ispravka'])],
            ['Preskočeno', count($kandidati['preskoceno'])],
        ]);
        $this->info("Lista: {$putanja}");

        if ($korisnik === null) {
            $this->warn('Ništa nije promenjeno. Posle pregleda liste pokreni ponovo sa --potvrdi --korisnik=<id>.');

            return self::SUCCESS;
        }

        $ispravljeno = $ispravka->primeni($kandidati['ispravka'], $korisnik);
        $this->info("Ispravljeno uređaja: {$ispravljeno} (autor: {$korisnik->name}).");

        return self::SUCCESS;
    }

    /**
     * @param  array{ispravka: list<array<string, mixed>>, preskoceno: list<array<string, mixed>>}  $kandidati
     */
    private function upisiCsv(array $kandidati): string
    {
        $redovi = [];
        foreach (['ispravka' => 'ISPRAVKA', 'preskoceno' => 'PRESKOČENO'] as $kljuc => $oznaka) {
            foreach ($kandidati[$kljuc] as $kandidat) {
                $redovi[] = [
                    $oznaka,
                    $kandidat['red'],
                    $kandidat['tid'],
                    $kandidat['sn'],
                    $kandidat['lokacijaId'],
                    $kandidat['lokacija'],
                    $kandidat['pib_excel'],
                    $kandidat['pib_aplikacija'],
                    $kandidat['datum_instalacije']?->format('d.m.Y'),
                    $kandidat['razlog'],
                ];
            }
        }

        return HalkCsvIzvestaj::upisi(
            $this->option('izlaz'),
            'halk_ispravka_statusa',
            ['Rezultat', 'Red u Excelu', 'TID', 'SN', 'lokacijaId', 'Lokacija', 'PIB Excel', 'PIB aplikacija', 'Datum instalacije', 'Razlog preskakanja'],
            $redovi
        );
    }
}
