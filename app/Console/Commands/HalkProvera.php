<?php

namespace App\Console\Commands;

use App\Actions\TerminalAcq\HalkCsvIzvestaj;
use App\Actions\TerminalAcq\HalkExcelCitac;
use App\Actions\TerminalAcq\HalkProvera as HalkProveraAction;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Faza 2 HALK evidencije: poređenje glavne tabele sa bazom. Samo čita, ne upisuje ništa.
 *
 * Lokalno:  php artisan halk:provera "beleske/HALK instalirani CELA TABELA.xlsx" --izlaz=beleske
 * Server:   /usr/local/bin/php artisan halk:provera /putanja/do/tabele.xlsx
 */
class HalkProvera extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'halk:provera
        {fajl : Putanja do HALK Excel tabele}
        {--sheet=HALK : Naziv sheet-a}
        {--izlaz= : Folder za CSV izveštaj (podrazumevano storage/app/halk)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'HALK: poredi glavnu Excel tabelu sa bazom i pravi CSV izveštaj (bez upisa u bazu)';

    /**
     * Execute the console command.
     */
    public function handle(HalkExcelCitac $citac, HalkProveraAction $provera): int
    {
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

        $nalazi = $provera->proveri($redovi);
        $putanja = $this->upisiCsv($nalazi);

        $this->info('Pročitano redova: '.count($redovi));
        $this->table(['#', 'Kategorija', 'Nalaza', 'Redova'], $this->rezime($nalazi));
        $this->info("Izveštaj: {$putanja}");

        return self::SUCCESS;
    }

    /**
     * @param  list<array<string, mixed>>  $nalazi
     * @return list<array{0: int, 1: string, 2: int, 3: int}>
     */
    private function rezime(array $nalazi): array
    {
        $redovi = [];
        foreach (HalkProveraAction::KATEGORIJE as $kategorija => $naziv) {
            $uKategoriji = array_filter($nalazi, fn (array $nalaz): bool => $nalaz['kategorija'] === $kategorija);
            if ($uKategoriji !== []) {
                $redovi[] = [$kategorija, $naziv, count($uKategoriji), count(array_unique(array_column($uKategoriji, 'red')))];
            }
        }

        return $redovi;
    }

    /**
     * @param  list<array<string, mixed>>  $nalazi
     */
    private function upisiCsv(array $nalazi): string
    {
        return HalkCsvIzvestaj::upisi(
            $this->option('izlaz'),
            'halk_provera',
            ['Red u Excelu', 'Kategorija', 'Naziv kategorije', 'TID', 'SN', 'lokacijaId', 'Excel', 'Aplikacija', 'Opis'],
            array_map(fn (array $nalaz): array => [
                $nalaz['red'],
                $nalaz['kategorija'],
                $nalaz['naziv'],
                $nalaz['tid'],
                $nalaz['sn'],
                $nalaz['lokacijaId'],
                $nalaz['excel'],
                $nalaz['aplikacija'],
                $nalaz['opis'],
            ], $nalazi)
        );
    }
}
