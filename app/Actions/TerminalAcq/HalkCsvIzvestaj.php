<?php

namespace App\Actions\TerminalAcq;

/**
 * CSV izveštaji HALK komandi: ';' i UTF-8 BOM, da se u Excelu otvore sa ispravnim slovima.
 */
class HalkCsvIzvestaj
{
    /**
     * @param  list<string>  $zaglavlje
     * @param  iterable<list<mixed>>  $redovi
     * @return string putanja upisanog fajla
     */
    public static function upisi(?string $folder, string $prefiks, array $zaglavlje, iterable $redovi): string
    {
        $folder = $folder ?: storage_path('app/halk');
        if (! is_dir($folder)) {
            mkdir($folder, 0775, true);
        }

        $putanja = rtrim($folder, '/').'/'.$prefiks.'_'.now()->format('Y-m-d_His').'.csv';
        $csv = fopen($putanja, 'w');
        fwrite($csv, "\xEF\xBB\xBF");
        fputcsv($csv, $zaglavlje, ';', '"', '');

        foreach ($redovi as $red) {
            fputcsv($csv, $red, ';', '"', '');
        }

        fclose($csv);

        return $putanja;
    }
}
