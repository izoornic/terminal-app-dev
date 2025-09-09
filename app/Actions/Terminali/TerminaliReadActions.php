<?php

namespace App\Actions\Terminali;
use App\Models\TerminalLokacija;
use Illuminate\Support\Facades\DB;

class TerminaliReadActions
{
    
    /**
     * TerminaliRead
     *
     * @param  mixed $search - array of search parameters
     * @param  mixed $sortField - field to sort by
     * @param  mixed $sortAsc - sort direction
     * @return void
     */
    public static function TerminaliRead($search, $sortField=null, $sortAsc=true)
    {
        // Extract search parameters
        $searchSB = $search['searchSB'] ?? null;
        $searchKutija = $search['searchKutija'] ?? null;
        $searchNazivLokacije = $search['searchNazivLokacije'] ?? null;
        $searchRegion = $search['searchRegion'] ?? null;
        $searchTipLokacije = $search['searchTipLokacije'] ?? null;
        $searchStatus = $search['searchStatus'] ?? null;
        $searchPib = $search['searchPib'] ?? null;
        //this one can be null, 1 or 2 ( 1-only blacklisted, 2-only fields that are set to null in db)
        $searchBlackist = $search['searchBlackist'] ?? null;

        $searchDistId = $search['searchDistributer'] ?? null;

        
        return TerminalLokacija::select(
            'lokacijas.*', 
            'terminals.id as tid', 
            'terminals.sn', 
            'terminals.broj_kutije', 
            'terminal_tips.model', 
            'lokacija_tips.lt_naziv', 
            'regions.r_naziv', 
            'terminal_status_tips.ts_naziv', 
            'terminal_status_tips.id as statusid', 
            'terminal_lokacijas.id as tlid', 
            'terminal_lokacijas.blacklist', 
            'terminal_lokacijas.distributerId',
            'terminal_lokacijas.br_komentara',
            )
        ->join('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
        ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
        ->leftJoin('terminal_tips', 'terminals.terminal_tipId', '=', 'terminal_tips.id')
        ->leftJoin('lokacija_tips', 'lokacijas.lokacija_tipId', '=', 'lokacija_tips.id')
        ->leftJoin('regions', 'regions.id', '=', 'lokacijas.regionId')
        ->leftJoin('terminal_status_tips','terminal_lokacijas.terminal_statusId', '=', 'terminal_status_tips.id')
        ->when($searchDistId, function ($query, $searchDistId) {
            return $query->where('terminal_lokacijas.distributerId', '=', $searchDistId);
        })
        ->when($searchSB, function ($query, $searchSB) {
            return $query->where('terminals.sn', 'like', '%' . $searchSB . '%');
        })
        ->when($searchKutija, function ($query, $searchKutija) {
            return $query->where('terminals.broj_kutije', 'like', '%' . $searchKutija . '%');
        })
        ->when($searchStatus, function ($query, $searchStatus) {
            return $query->where('terminal_lokacijas.terminal_statusId', '=', $searchStatus);
        })
        ->when($searchNazivLokacije, function ($query, $searchNazivLokacije) {
            return $query->where('lokacijas.l_naziv', 'like', '%' .$searchNazivLokacije . '%')
                         ->orWhere('lokacijas.adresa', 'like', '%' . $searchNazivLokacije . '%')    
                         ->orWhere('lokacijas.mesto', 'like', '%' . $searchNazivLokacije . '%');
        })
        ->when($searchRegion, function ($query, $searchRegion) {
            return $query->where('lokacijas.regionId', '=', $searchRegion);
        })
        ->when($searchTipLokacije, function ($query, $searchTipLokacije) {
            return $query->where('lokacijas.lokacija_tipId', '=' , $searchTipLokacije);
        })
        ->when($searchPib, function ($query, $searchPib) {
            return $query->where('lokacijas.pib', 'like', '%' . $searchPib . '%');
        })
        ->when(!is_null($searchBlackist) && $searchBlackist == 1, function ($query) {
            return $query->where('terminal_lokacijas.blacklist', '=', 1);
        })
        ->when(!is_null($searchBlackist) && $searchBlackist == 2, function ($query) {
            return $query->whereNull('terminal_lokacijas.blacklist');
        });
        //->orderBy($sortField, $sortAsc ? 'asc' : 'desc');
    }

    public static function DistributerTerminaliRead($distributerId, $search)
    {
        // Extract search parameters
        $distId = $distributerId;
        
        $searchSB = $search['searchSB'] ?? null;
        $searchKutija = $search['searchKutija'] ?? null;
        $searchLokacija = $search['searchLokacija'] ?? null;
        $searchTipLicence = $search['searchTipLicence'] ?? null;
        $searchNenaplativ = $search['searchNenaplativ'] ?? null;
        $searchPib = $search['searchPib'] ?? null;
        
        return TerminalLokacija::select(
            'terminal_lokacijas.id', 
            'terminal_lokacijas.br_komentara',
            'terminals.sn', 
            'lokacijas.l_naziv', 
            'lokacijas.l_naziv_sufix', 
            'lokacijas.is_duplicate',
            'lokacijas.mesto', 
            'lokacijas.adresa', 
            'lokacijas.latitude', 
            'lokacijas.longitude',
            'lokacijas.pib',
            'lokacijas.id as lokid',
            'licenca_naplatas.id as lnid', 
            'licenca_naplatas.datum_pocetka_licence', 
            'licenca_naplatas.datum_kraj_licence',
            'licenca_naplatas.nenaplativ',
            'licenca_naplatas.zaduzeno', 
            'licenca_naplatas.razduzeno',
            'licenca_naplatas.nenaplativ',
            'licenca_naplatas.dist_zaduzeno',
            'licenca_naplatas.dist_razduzeno',
            'licenca_tips.licenca_naziv', 
            'licenca_tips.id as ltid', 
            'licenca_tips.broj_parametara_licence')
            ->leftJoin('licenca_naplatas', function($join)
                {
                    $join->on('licenca_naplatas.terminal_lokacijaId', '=', 'terminal_lokacijas.id');
                    $join->on('licenca_naplatas.aktivna', '=', DB::raw("1"));
                })
            ->leftJoin('terminals', 'terminal_lokacijas.terminalId', '=', 'terminals.id')
            ->leftJoin('lokacijas', 'terminal_lokacijas.lokacijaId', '=', 'lokacijas.id')
            ->leftJoin('licenca_distributer_cenas', 'licenca_naplatas.licenca_distributer_cenaId', '=', 'licenca_distributer_cenas.id')
            ->leftJoin('licenca_tips', 'licenca_distributer_cenas.licenca_tipId', '=', 'licenca_tips.id')
            ->when($searchSB, function ($query, $searchSB) {
                return $query->where('terminals.sn', 'like', '%' . $searchSB . '%');
            })
            ->when($searchKutija, function ($query, $searchKutija) {
                return $query->where('terminals.broj_kutije', 'like', '%' . $searchKutija . '%');
            })
            ->when($searchLokacija, function ($rtval, $searchLokacija){
                return $rtval->where('lokacijas.mesto', 'like', '%'.$searchLokacija.'%')
                    ->orWhere('lokacijas.adresa', 'like', '%'.$searchLokacija.'%')
                    ->orWhere('lokacijas.l_naziv', 'like', '%'.$searchLokacija.'%');
            })
            ->when($searchTipLicence, function ($rtval, $searchTipLicence){
                return $rtval->where('licenca_distributer_cenas.id', '=', ($searchTipLicence == 1000) ? null : $searchTipLicence);
            })
            ->when($searchNenaplativ, function ($rtval, $searchNenaplativ){
                return $rtval->where('licenca_naplatas.nenaplativ', '=', 1);
            })
            ->when($searchPib, function ($query, $searchPib) {
                return $query->where('lokacijas.pib', 'like', '%'.$searchPib.'%');
            })
            ->where('terminal_lokacijas.distributerId', '=', $distId)
            ->orderBy(\DB::raw("COALESCE(licenca_naplatas.datum_kraj_licence, '9999-12-31')", 'ASC'))
            ->orderBy('terminal_lokacijas.id')
            ->orderBy('licenca_distributer_cenas.licenca_tipId');
    }
   
}