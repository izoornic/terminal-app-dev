<?php

namespace App\Actions\Bankomati;
use App\Models\Blokacija;
use Illuminate\Support\Facades\DB;

class BankomatiLokacijeReadActions
{
    public static function blokacijeRead($search, $sortField=null, $sortAsc='desc') 
    {

        // Extract search parameters
        $searchNaziv = $search['naziv'] ?? null;
        $searchAdresa = $search['adresa'] ?? null;
        $searchRegion = $search['region'] ?? null;
        $searchTipLokacije = $search['tip_lokacije'] ?? null;
        $searchPib = $search['pib'] ?? null;

        if(!$sortField) $sortField='blokacijas.id';

        return Blokacija::select(
            'blokacijas.*', 
            'blokacija_tips.id as tipid',
            'blokacija_tips.bl_tip_naziv', 
            'bankomat_regions.r_naziv',
            'blokacija_kontakt_osobas.ime',
            'blokacija_kontakt_osobas.telefon',
            'blokacija_kontakt_osobas.email as kontakt_email')
        ->join('blokacija_tips', 'blokacijas.blokacija_tip_id', '=', 'blokacija_tips.id')
        ->join('bankomat_regions', 'blokacijas.bankomat_region_id', '=', 'bankomat_regions.id')
        ->leftJoin('blokacija_kontakt_osobas', 'blokacijas.id', '=', 'blokacija_kontakt_osobas.blokacija_id')
        ->when($searchNaziv, function ($query, $searchNaziv) {
            return $query->where('blokacijas.bl_naziv', 'like', '%' . $searchNaziv . '%');
        })
        ->when($searchRegion, function ($query, $searchRegion) {
            return $query->where('blokacijas.bankomat_region_id', '=', $searchRegion);
        })
        ->when($searchTipLokacije, function ($query, $searchTipLokacije) {
            return $query->where('blokacijas.blokacija_tip_id', '=', $searchTipLokacije);
        })
        ->when($searchPib, function ($query, $searchPib) {
            return $query->where('blokacijas.pib', 'like', '%' . $searchPib . '%');
        })
        ->when($searchAdresa, function ($query, $searchAdresa) {
            return $query->where('blokacijas.bl_mesto', 'like', '%' . $searchAdresa . '%');
                         //->orWhere('blokacijas.bl_adresa', 'like', '%' . $searchAdresa . '%');
        })
        ->groupBy('blokacijas.id')
        ->orderBy($sortField, $sortAsc);
    }
}
    