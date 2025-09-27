<?php

namespace App\Actions\Bankomati;
use App\Models\BankomatLokacija;
use Illuminate\Support\Facades\DB;

class BankomatiReadActions
{
    
    /**
     * TerminaliRead
     *
     * @param  mixed $search - array of search parameters
     * @param  mixed $sortField - field to sort by
     * @param  mixed $sortAsc - sort direction
     * @return void
     */
    public static function BankomatiRead($search, $sortField=null, $sortAsc=true)
    {
        // Extract search parameters
        $searchSB = $search['b_sn'] ?? null;
        $searchTerminalId = $search['b_terminal_id'] ?? null;
        $searchBankomatModel = $search['proizvod_model'] ?? null;
        $searchNazivLokacije = $search['lokacija_naziv'] ?? null;
        $searchRegion = $search['region'] ?? null;
        $searchStatus = $search['status'] ?? null;
        $searchTipLokacije = $search['tip'] ?? null;
        $searchPib = $search['pib'] ?? null;
        $searchProduct = $search['product_tip'] ?? null;

        if(!$sortField) $sortField='bankomats.id';
        
        return BankomatLokacija::select(
            'blokacijas.*', 
            'bankomats.id as bid', 
            'bankomats.b_sn',
            'bankomats.b_terminal_id', 
            'bankomat_tips.model', 
            'blokacija_tips.id as tipid',
            'blokacija_tips.bl_tip_naziv', 
            'bankomat_regions.r_naziv', 
            'bankomat_status_tips.status_naziv', 
            'bankomat_status_tips.id as statusid', 
            'bankomat_lokacijas.id as blid',
            'bankomat_product_tips.bp_tip_naziv',
            'bankomat_product_tips.id as bptipid'
            )
        ->join('bankomats', 'bankomat_lokacijas.bankomat_id', '=', 'bankomats.id')
        ->join('blokacijas', 'bankomat_lokacijas.blokacija_id', '=', 'blokacijas.id')
        ->leftJoin('bankomat_tips', 'bankomats.bankomat_tip_id', '=', 'bankomat_tips.id')
        ->leftJoin('bankomat_product_tips', 'bankomat_tips.bankomat_produkt_tip_id', '=', 'bankomat_product_tips.id')
        ->leftJoin('blokacija_tips', 'blokacijas.blokacija_tip_id', '=', 'blokacija_tips.id')
        ->leftJoin('bankomat_regions', 'blokacijas.bankomat_region_id', '=', 'bankomat_regions.id')
        ->leftJoin('bankomat_status_tips','bankomat_lokacijas.bankomat_status_tip_id', '=', 'bankomat_status_tips.id')
        ->when($searchSB, function ($query, $searchSB) {
            return $query->where('bankomats.b_sn', 'like', '%' . $searchSB . '%');
        })
        ->when($searchTerminalId, function ($query, $searchTerminalId) {
            return $query->where('bankomats.b_terminal_id', 'like', '%' . $searchTerminalId . '%');
        })
        ->when($searchBankomatModel, function ($query, $searchBankomatModel) {
            return $query->where('bankomat_tips.model', 'like',  '%' . $searchBankomatModel. '%');
        })
        ->when($searchNazivLokacije, function ($query, $searchNazivLokacije) {
            return $query->where('blokacijas.bl_naziv', 'like', '%' .$searchNazivLokacije . '%');
                         /* ->orWhere('lokacijas.adresa', 'like', '%' . $searchNazivLokacije . '%')    
                         ->orWhere('lokacijas.mesto', 'like', '%' . $searchNazivLokacije . '%'); */
        })
         ->when($searchRegion, function ($query, $searchRegion) {
            return $query->where('blokacijas.bankomat_region_id', '=', $searchRegion);
        })
        ->when($searchStatus, function ($query, $searchStatus) {
            return $query->where('bankomat_lokacijas.bankomat_status_tip_id', '=' , $searchStatus);
        })
        ->when($searchTipLokacije, function ($query, $searchTipLokacije) {
            return $query->where('blokacijas.blokacija_tip_id', '=' , $searchTipLokacije);
        })
        ->when($searchPib, function ($query, $searchPib) {
            return $query->where('blokacijas.pib', 'like', '%' . $searchPib . '%');
        })
        ->when($searchProduct, function ($query, $searchProduct) {
            return $query->where('bankomat_product_tips.id', '=', $searchProduct);
        })
        ->orderBy($sortField, $sortAsc ? 'asc' : 'desc');
    }
}