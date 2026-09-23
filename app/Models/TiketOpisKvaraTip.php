<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TiketOpisKvaraTip extends Model
{
    use HasFactory;

    /**
     * Lista vrsta kvara ne zavisi od tipa terminala, sve vrste se upisuju s ovim tipom
     */
    public const TERMINAL_TIP = 1;

    /**
     * Online prijava s ovom vrstom kvara se automatski dodeljuje šefu servisa regiona (Prijava.php)
     */
    public const NADLEZNOST_SEF_SERVISA = 1;

    /**
     * Online prijava s ovom vrstom kvara ostaje nedodeljena, preuzima je call centar
     */
    public const NADLEZNOST_CALL_CENTAR = 2;

    /**
     * fillable
     *
     * @var list<string>
     */
    protected $fillable = [
        'termnal_tipId',
        'tok_naziv',
        'tok_dodela_nadleznostiId',
        'list_order',
    ];

    public static function opisList($id = 1)
    {
        //ne bas tako sjajan hak
        //Lista vise ne zavisi od ID-a tipa terminala
        // where('termnal_tipId', '=', $id)
        $kvar_list =[];
        foreach(TiketOpisKvaraTip::orderBy('list_order')->get() as $kvar){
            $kvar_list[$kvar->id] = $kvar->tok_naziv;
        }
        return  $kvar_list;
    }

    /**
     * Nazivi nadležnosti za prikaz
     *
     * @return array<int, string>
     */
    public static function nadleznosti(): array
    {
        return [
            self::NADLEZNOST_SEF_SERVISA => 'Šef servisa (automatska dodela)',
            self::NADLEZNOST_CALL_CENTAR => 'Call centar',
        ];
    }

    /**
     * Tiketi otvoreni s ovom vrstom kvara
     */
    public function tiketi(): HasMany
    {
        return $this->hasMany(Tiket::class, 'opis_kvaraId');
    }

    /**
     * Koraci koje serviser preuzima za ovu vrstu kvara (prikaz na tiketview)
     */
    public function akcije(): HasMany
    {
        return $this->hasMany(TiketOpisAkcijaIndex::class, 'tiket_opis_kvaraId');
    }
}
