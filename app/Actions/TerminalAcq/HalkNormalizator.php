<?php

namespace App\Actions\TerminalAcq;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Normalizacija vrednosti iz HALK Excel tabela (glavna tabela i zahtevi banke).
 *
 * Čiste funkcije bez pristupa bazi; koriste ih halk:provera, halk:uvoz i uvoz zahteva.
 */
class HalkNormalizator
{
    public const MID_DUZINA = 15;

    /**
     * Excel status ("Instalirana") → naziv statusa; "Testni" nije status nego osobina TID-a.
     *
     * @var array<string, string>
     */
    private const STATUSI = [
        'instaliran' => 'Instaliran',
        'za instalaciju' => 'Za instalaciju',
        'deinstaliran' => 'Deinstaliran',
        'prekonfigurisan' => 'Prekonfigurisan',
        'opozvano' => 'Opozvan',
        'opozvan' => 'Opozvan',
        'testni' => 'Testni',
    ];

    /**
     * ACQ Terminal ID iz Excela.
     *
     * "PN001158/PN002927" je stari/novi TID; sufiks "TEST"/"testni" znači testni TID.
     *
     * @return array{tid: ?string, stari_tid: ?string, testni: bool, ispravan: bool}
     */
    public static function tid(mixed $vrednost): array
    {
        $tekst = strtoupper(trim((string) $vrednost));
        $testni = (bool) preg_match('/TEST(NI)?\b/', $tekst);

        preg_match_all('/PN\s*(\d{6})/', $tekst, $pogodci);
        $tidovi = array_map(fn (string $cifre): string => 'PN'.$cifre, $pogodci[1]);

        if ($tidovi === []) {
            return ['tid' => null, 'stari_tid' => null, 'testni' => $testni, 'ispravan' => false];
        }

        $tid = end($tidovi);
        $stariTid = count($tidovi) > 1 ? $tidovi[0] : null;

        return ['tid' => $tid, 'stari_tid' => $stariTid, 'testni' => $testni, 'ispravan' => true];
    }

    /**
     * MID kao tekst od 15 cifara (vodeće nule). Kod "MID/MID" uzima se novi (poslednji).
     */
    public static function mid(mixed $vrednost): ?string
    {
        $delovi = preg_split('/[\/,;]/', (string) $vrednost);
        $cifre = preg_replace('/\D/', '', (string) end($delovi));

        if ($cifre === '') {
            return null;
        }

        return str_pad($cifre, self::MID_DUZINA, '0', STR_PAD_LEFT);
    }

    public static function pib(mixed $vrednost): ?string
    {
        return self::samoCifre($vrednost);
    }

    public static function ptt(mixed $vrednost): ?string
    {
        return self::samoCifre($vrednost);
    }

    public static function mcc(mixed $vrednost): ?string
    {
        $cifre = self::samoCifre($vrednost);

        return $cifre === null ? null : str_pad($cifre, 4, '0', STR_PAD_LEFT);
    }

    /**
     * PIB: 9 cifara, kontrolna cifra po ISO 7064 MOD 11,10.
     */
    public static function pibIspravan(?string $pib): bool
    {
        if ($pib === null || ! preg_match('/^\d{9}$/', $pib)) {
            return false;
        }

        $zbir = 10;
        for ($i = 0; $i < 8; $i++) {
            $zbir = ($zbir + (int) $pib[$i]) % 10;
            $zbir = (($zbir === 0 ? 10 : $zbir) * 2) % 11;
        }

        return (11 - $zbir) % 10 === (int) $pib[8];
    }

    /**
     * wifi / WiFI / WI FI → WIFI, GRS → GPRS, ETHERNET / LAN → ETH.
     */
    public static function komunikacija(mixed $vrednost): ?string
    {
        $tekst = strtoupper(preg_replace('/[\s\-_]+/', '', (string) $vrednost));

        return match (true) {
            $tekst === '' => null,
            str_contains($tekst, 'WIFI') => 'WIFI',
            in_array($tekst, ['GPRS', 'GRS', 'GSM', '3G', '4G'], true) => 'GPRS',
            in_array($tekst, ['ETH', 'ETHERNET', 'LAN'], true) => 'ETH',
            default => $tekst,
        };
    }

    public static function telefon(mixed $vrednost): ?string
    {
        return self::tekst($vrednost);
    }

    /**
     * Telefon za lokacija_kontakt_osobas: kao u formi lokacije, "+381" umesto vodeće nule
     * ("066 500 000 2" → "+38166 500 000 2"). Već međunarodni broj ostaje kakav jeste.
     */
    public static function telefonZaKontakt(mixed $vrednost): ?string
    {
        $telefon = self::telefon($vrednost);
        if ($telefon === null || str_starts_with($telefon, '+')) {
            return $telefon;
        }

        if (str_starts_with($telefon, '00')) {
            return '+'.substr($telefon, 2);
        }

        return str_starts_with($telefon, '0') ? '+381'.substr($telefon, 1) : $telefon;
    }

    /**
     * Inventarni broj / ICCID: broj iz Excela kao tekst (bez ".0"), tekst ostaje tekst.
     */
    public static function oznaka(mixed $vrednost): ?string
    {
        if (is_float($vrednost) && floor($vrednost) === $vrednost) {
            $vrednost = number_format($vrednost, 0, '', '');
        }

        return self::tekst($vrednost);
    }

    /**
     * Trim i jedan razmak umesto više; prazno → null.
     */
    public static function tekst(mixed $vrednost): ?string
    {
        $tekst = trim(preg_replace('/\s+/u', ' ', (string) $vrednost));

        return $tekst === '' ? null : $tekst;
    }

    /**
     * Adresa ili mesto za poređenje: bez dijakritika, razmaka i interpunkcije, malim slovima.
     */
    public static function uporediviTekst(?string $vrednost): string
    {
        return preg_replace('/[^a-z0-9]/', '', Str::lower(Str::ascii((string) $vrednost)));
    }

    public static function sn(mixed $vrednost): ?string
    {
        $tekst = strtoupper(trim((string) $vrednost));

        return $tekst === '' ? null : $tekst;
    }

    /**
     * Status iz kolone "Instalirana", bez obzira na veličinu slova; nepoznat → null.
     */
    public static function status(mixed $vrednost): ?string
    {
        return self::STATUSI[mb_strtolower(trim((string) $vrednost))] ?? null;
    }

    /**
     * Status reda glavne tabele. Kad je ćelija prazna, izvodi se kao u Excel formuli
     * (=IF(ISBLANK(datum instalacije),"Za instalaciju","Instaliran")), a napomena
     * "OPOZVANO!" u koloni SN znači opozvan TID.
     *
     * @param  array<string, mixed>  $red  red iz HalkExcelCitac
     * @return array{status: string, izveden: bool}
     */
    public static function statusReda(array $red): array
    {
        $status = self::status($red['status'] ?? null);
        if ($status !== null) {
            return ['status' => $status, 'izveden' => false];
        }

        $sn = self::sn($red['sn'] ?? null);

        $status = match (true) {
            $sn !== null && str_contains($sn, 'OPOZVAN') => 'Opozvan',
            self::datum($red['datum_instalacije'] ?? null) !== null => 'Instaliran',
            default => 'Za instalaciju',
        };

        return ['status' => $status, 'izveden' => true];
    }

    /**
     * SN uređaja; napomene upisane u kolonu SN ("OPOZVANO!", rečenice) nisu SN → null.
     */
    public static function ispravanSn(mixed $vrednost): ?string
    {
        $sn = self::sn($vrednost);

        return $sn !== null && preg_match('/^[A-Z0-9][A-Z0-9\-]{5,}$/', $sn) ? $sn : null;
    }

    /**
     * Excel serijski broj ili tekst "d.m.yyyy" → datum.
     */
    public static function datum(mixed $vrednost): ?CarbonImmutable
    {
        if (is_int($vrednost) || is_float($vrednost) || (is_string($vrednost) && is_numeric($vrednost))) {
            return CarbonImmutable::instance(ExcelDate::excelToDateTimeObject((float) $vrednost))->startOfDay();
        }

        $tekst = trim((string) $vrednost);
        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})\.?$/', $tekst, $delovi)) {
            return CarbonImmutable::create((int) $delovi[3], (int) $delovi[2], (int) $delovi[1])->startOfDay();
        }

        return null;
    }

    /**
     * Plus kod (npr. "VGC9+45 Mrčajevci") je podatak lokacije.
     */
    public static function jePlusKod(?string $vrednost): bool
    {
        return $vrednost !== null && (bool) preg_match('/^[23456789CFGHJMPQRVWX]{4,8}\+[23456789CFGHJMPQRVWX]{2,3}\b/i', $vrednost);
    }

    private static function samoCifre(mixed $vrednost): ?string
    {
        if (is_float($vrednost) && floor($vrednost) === $vrednost) {
            $vrednost = (int) $vrednost;
        }

        $cifre = preg_replace('/\D/', '', (string) $vrednost);

        return $cifre === '' ? null : $cifre;
    }
}
