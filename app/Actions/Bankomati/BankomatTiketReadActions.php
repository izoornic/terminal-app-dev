<?php

namespace App\Actions\Bankomati;

use App\Models\BankomatTiket;   

class BankomatTiketReadActions
{
     /**
     * read
     *
     * @param  mixed $search - array of search parameters
     * @param  mixed $sortField - field to sort by
     * @param  mixed $sortAsc - sort direction
     * @return void
     */
    public static function read($search, $sortField=null, $sortAsc=false)
    {
        
       // Extract search parameters
        $searchProductTip = $search['searchProductTip'] ?? null;
        $searchStatus = $search['searchStatus'] ?? null;
        $searchLokacijaNaziv = $search['searchLokacijaNaziv'] ?? null;
        $searchMesto = $search['searchMesto'] ?? null;
        $searchRegion = $search['searchRegion'] ?? null;
        $dodeljeniUsersIds = $search['serviseri'] ?? null;
        $searchUsersRegion = $search['searchUsersRegion'] ?? null;
        
        if($searchStatus == 'Svi')  $searchStatus = null;

        if(!$sortField) $sortField='bankomat_tikets.id';

        return BankomatTiket::select(
            'bankomat_tikets.*', 
            'bankomats.id as bid', 
            'bankomats.b_sn',
            'bankomats.b_terminal_id', 
            'bankomat_tips.model', 
            'bankomat_tiket_prioritet_tips.id as tipid',
            'bankomat_tiket_prioritet_tips.btpt_naziv',
            'bankomat_tiket_prioritet_tips.btn_collor',
            'bankomat_tiket_prioritet_tips.btn_hover_collor',	
            'bankomat_tiket_prioritet_tips.tr_bg_collor',
            'bankomat_product_tips.bp_tip_naziv',
            'blokacijas.is_duplicate',
            'blokacijas.bl_naziv',
            'blokacijas.bl_naziv_sufix',
            'blokacijas.bl_mesto',
            'bankomat_regions.r_naziv',
            'users.name',
            'bankomat_tiket_kvar_tips.btkt_naziv',
        )
        ->join('bankomat_lokacijas', 'bankomat_tikets.bankoamt_lokacija_id', '=', 'bankomat_lokacijas.id')
        ->join('bankomats', 'bankomats.id', '=', 'bankomat_lokacijas.bankomat_id')
        ->leftJoin('users', 'users.id', '=', 'bankomat_tikets.user_dodeljen_id')
        ->leftJoin('bankomat_tiket_kvar_tips', 'bankomat_tikets.bankomat_tiket_kvar_tip_id', '=', 'bankomat_tiket_kvar_tips.id')
        ->join('blokacijas', 'bankomat_lokacijas.blokacija_id', '=', 'blokacijas.id')
        ->join('bankomat_regions', 'blokacijas.bankomat_region_id', '=', 'bankomat_regions.id')
        ->join('bankomat_tips', 'bankomats.bankomat_tip_id', '=', 'bankomat_tips.id')
        ->join('bankomat_product_tips', 'bankomat_tips.bankomat_produkt_tip_id', '=', 'bankomat_product_tips.id')
        ->join('bankomat_tiket_prioritet_tips', 'bankomat_tikets.bankomat_tiket_prioritet_id', '=', 'bankomat_tiket_prioritet_tips.id')
        ->when($dodeljeniUsersIds, function ($query) use ($dodeljeniUsersIds, $searchUsersRegion) {
            return $query->whereIn('bankomat_tikets.user_dodeljen_id', $dodeljeniUsersIds)
                        ->where('bankomat_regions.id', '<>' ,$searchUsersRegion);
        })
        ->when($searchProductTip, function ($query, $searchProductTip) {
            return $query->where('bankomat_product_tips.id', '=', $searchProductTip);
        })
        ->when($searchLokacijaNaziv, function ($query, $searchLokacijaNaziv) {
            return $query->where('blokacijas.bl_naziv', 'like', '%'.$searchLokacijaNaziv.'%');
        })
        ->when($searchMesto, function ($query, $searchMesto) {
            return $query->where('blokacijas.bl_mesto', 'like', '%'.$searchMesto.'%');
        })
        ->when($searchRegion, function ($query, $searchRegion) {
            return $query->where('bankomat_regions.id', $searchRegion);
        })
        ->when($searchStatus, function ($query, $searchStatus) {
            if($searchStatus == 'Aktivan'){
                 return $query->whereIn('bankomat_tikets.status', ['Otvoren', 'Dodeljen']);
            }else{
                return $query->where('bankomat_tikets.status', '=', $searchStatus);
            }     
        })
        ->orderBy($sortField, $sortAsc ? 'asc' : 'desc');
    }
}