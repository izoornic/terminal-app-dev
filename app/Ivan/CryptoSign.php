<?php
namespace App\Ivan;

use App\Models\LicencaSignLog;
use Spatie\Crypto\Rsa\PrivateKey;
use Illuminate\Support\Carbon; 

class CryptoSign 
{
    /**
     * [Description for criptSignature]
     *
     * @param array $vals_ins
     * 
     * $vals_ins = [
     *      'mesecId'=> 'int', -> NE ULAZI U KLJC!!!
     *      'terminal_sn' => 'string',
     *      'datum_pocetak' => 'Y-m-d',
     *      'datum_kraj' => 'Y-m-d',
     *      'datum_prekoracenja' =>'Y-m-d'];
     *      'naziv_licence' => 'string'
     * 
     * @return string
     * 
     */
    public static function criptSignature($vals_ins)
    {  
        $datum_kraj = Carbon::parse($vals_ins['datum_kraj'])->format('Y-m-d');
        $datum_prekoracenja = Carbon::parse($vals_ins['datum_prekoracenja'])->format('Y-m-d');
        //dd($vals_ins, $datum_kraj, $datum_prekoracenja);
        $string_signature = $vals_ins['naziv_licence'].'-'.$vals_ins['terminal_sn'].'-'.$datum_kraj.'-'.$datum_prekoracenja;
        LicencaSignLog::create([
            'terminal_sn' => $vals_ins['terminal_sn'],
            'signature' => $string_signature
        ]);
        //dd($string_signature);
        $pathToPrivateKey = base_path().'/storage/app/lickey/lic_private';
        return PrivateKey::fromFile($pathToPrivateKey)->sign($string_signature);
    }

        //$key_path = base_path().'/storage/app/lickey/';
        //(new KeyPair())->generate($key_path."lic_private", $key_path."lic_public");
        //
        /* $terminal = Terminal::where('sn', $snn) -> first();
        $str_to_sign = 'zeta-epos-2023-02-22';
       
        $pathToPrivateKey = base_path().'/storage/app/lickey/lic_private';
        $signature = PrivateKey::fromFile($pathToPrivateKey)->sign($str_to_sign); */

}
