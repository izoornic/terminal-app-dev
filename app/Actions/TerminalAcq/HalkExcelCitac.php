<?php

namespace App\Actions\TerminalAcq;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;

/**
 * Čita HALK Excel (glavnu tabelu ili zahtev banke) i kolone mapira po nazivu zaglavlja.
 *
 * Pozicije kolona se razlikuju (u zahtevu je kolona A prazna i postoji "Model
 * terminala"), pa se red zaglavlja traži po ćeliji "ACQ Terminal ID". Formule
 * (npr. kolona "Instalirana") se čitaju kao izračunate vrednosti.
 */
class HalkExcelCitac
{
    private const ZAGLAVLJE_TRAZI_REDOVA = 10;

    /**
     * Normalizovan naziv zaglavlja → ključ u redu. Ključ sa '*' na kraju znači "počinje sa".
     *
     * @var array<string, string>
     */
    private const KOLONE = [
        'acq terminal id' => 'acq_tid',
        'acq merchant id' => 'acq_mid',
        'naziv trgovca' => 'naziv_trgovca',
        'naziv lokacije - na slipu' => 'naziv_na_slipu',
        'adresa lokacije' => 'adresa',
        'mesto lokacije' => 'mesto',
        'ptt' => 'ptt',
        'mid/šifra trgovca' => 'sifra_trgovca',
        'mcc' => 'mcc',
        'pib' => 'pib',
        'telefon' => 'telefon',
        'kontakt osoba' => 'kontakt',
        'model terminala' => 'model',
        'komunikacija' => 'komunikacija',
        'dodatne opcije*' => 'dodatne_opcije',
        'datum zahteva' => 'datum_zahteva',
        'datum instalacije' => 'datum_instalacije',
        'sn uređaja' => 'sn',
        'inventarni br.' => 'inventarni_broj',
        'iccid sim-a' => 'iccid',
        'instalirana' => 'status',
        'precizna lokacija*' => 'precizna_lokacija',
    ];

    /**
     * @param  list<string>  $obavezneKolone  ključevi iz KOLONE koji moraju postojati u zaglavlju
     * @return list<array<string, mixed>> redovi sa ključem 'red' (broj reda u Excelu) i svim poznatim kolonama
     *
     * @throws RuntimeException kad fajl nema sheet sa zaglavljem ili nedostaju obavezne kolone
     */
    public function procitaj(string $putanja, array $obavezneKolone, ?string $sheet = null): array
    {
        $citac = IOFactory::createReaderForFile($putanja);
        $citac->setReadDataOnly(true);
        $knjiga = $citac->load($putanja);

        $listovi = $sheet !== null ? [$knjiga->getSheetByName($sheet)] : $knjiga->getAllSheets();

        foreach (array_filter($listovi) as $list) {
            $zaglavlje = $this->nadjiZaglavlje($list);
            if ($zaglavlje === null) {
                continue;
            }

            [$redZaglavlja, $kolone] = $zaglavlje;
            $nedostaju = array_diff($obavezneKolone, $kolone);
            if ($nedostaju !== []) {
                throw new RuntimeException('Nedostaju kolone: '.implode(', ', $this->naziviKolona($nedostaju)));
            }

            return $this->redovi($list, $redZaglavlja, $kolone);
        }

        throw new RuntimeException($sheet !== null
            ? "Sheet \"{$sheet}\" ne postoji ili nema zaglavlje sa kolonom \"ACQ Terminal ID\"."
            : 'Nijedan sheet nema zaglavlje sa kolonom "ACQ Terminal ID".');
    }

    /**
     * @return array{0: int, 1: array<string, string>}|null red zaglavlja i mapa kolona (slovo → ključ)
     */
    private function nadjiZaglavlje(Worksheet $list): ?array
    {
        $poslednjaKolona = $list->getHighestDataColumn();
        $poslednjiRed = min($list->getHighestDataRow(), self::ZAGLAVLJE_TRAZI_REDOVA);

        for ($red = 1; $red <= $poslednjiRed; $red++) {
            $kolone = [];
            foreach ($list->getRowIterator($red, $red)->current()->getCellIterator('A', $poslednjaKolona) as $celija) {
                $kljuc = $this->kljucZaglavlja((string) $celija->getValue());
                if ($kljuc !== null && ! in_array($kljuc, $kolone, true)) {
                    $kolone[$celija->getColumn()] = $kljuc;
                }
            }

            if (in_array('acq_tid', $kolone, true)) {
                return [$red, $kolone];
            }
        }

        return null;
    }

    /**
     * @param  array<string, string>  $kolone
     * @return list<array<string, mixed>>
     */
    private function redovi(Worksheet $list, int $redZaglavlja, array $kolone): array
    {
        $redovi = [];
        $poslednjiRed = $list->getHighestDataRow();

        for ($red = $redZaglavlja + 1; $red <= $poslednjiRed; $red++) {
            $vrednosti = ['red' => $red];
            $prazan = true;

            foreach ($kolone as $slovo => $kljuc) {
                $vrednost = $list->getCell($slovo.$red)->getCalculatedValue();
                $vrednosti[$kljuc] = $vrednost;
                if ($vrednost !== null && trim((string) $vrednost) !== '' && $kljuc !== 'status') {
                    $prazan = false;
                }
            }

            if (! $prazan) {
                $redovi[] = $vrednosti;
            }
        }

        return $redovi;
    }

    private function kljucZaglavlja(string $naziv): ?string
    {
        $naziv = mb_strtolower(trim(preg_replace('/\s+/u', ' ', $naziv)));
        if ($naziv === '') {
            return null;
        }

        foreach (self::KOLONE as $zaglavlje => $kljuc) {
            if (str_ends_with($zaglavlje, '*') ? str_starts_with($naziv, rtrim($zaglavlje, '*')) : $naziv === $zaglavlje) {
                return $kljuc;
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $kljucevi
     * @return list<string>
     */
    private function naziviKolona(array $kljucevi): array
    {
        $nazivi = [];
        foreach ($kljucevi as $kljuc) {
            $nazivi[] = (string) array_search($kljuc, self::KOLONE, true);
        }

        return array_map(fn (string $naziv): string => rtrim($naziv, '*'), $nazivi);
    }
}
