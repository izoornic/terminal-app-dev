<?php

namespace App\Actions\Bankomati;
use App\Models\BankomatLokacija;
use Illuminate\Support\Facades\DB;

class BankomatInformation
{
    public static function bankomatInfo($bankomat_lokacija_id)
    {
        $bankomat_lokacija = BankomatLokacija::select( 
            'bankomat_lokacijas.id', 
            'bankomat_lokacijas.updated_at as last_updated', 
            'bankomats.id as bid', 
            'bankomats.b_sn',  
            'bankomats.b_terminal_id',
            'blokacijas.bl_naziv as blokacija_naziv',
            'blokacijas.bl_naziv_sufix as blokacija_naziv_sufix',
            'blokacijas.bl_adresa as blokacija_adresa',
            'blokacijas.bl_mesto as blokacija_mesto',
            'blokacija_tips.bl_tip_naziv as blokacija_tip_naziv',
            'blokacijas.pib',
            'blokacijas.mb',
            'blokacijas.email',
            'bankomat_tips.model',
            'bankomat_tips.proizvodjac',
            'bankomat_tips.bankomat_produkt_tip_id',
            'bankomat_regions.r_naziv',
            'bankomat_status_tips.status_naziv',
            'bankomat_product_tips.bp_tip_naziv',
            )
            ->leftJoin('bankomats','bankomat_lokacijas.bankomat_id', '=', 'bankomats.id')
            ->leftJoin('blokacijas', 'bankomat_lokacijas.blokacija_id', '=', 'blokacijas.id')
            ->leftJoin('bankomat_tips', 'bankomats.bankomat_tip_id', '=', 'bankomat_tips.id')
            ->leftJoin('bankomat_product_tips', 'bankomat_tips.bankomat_produkt_tip_id', '=', 'bankomat_product_tips.id')
            ->leftJoin('blokacija_tips', 'blokacijas.blokacija_tip_id', '=', 'blokacija_tips.id')
            ->leftJoin('bankomat_regions', 'blokacijas.bankomat_region_id', '=', 'bankomat_regions.id')
            ->leftJoin('bankomat_status_tips', 'bankomat_lokacijas.bankomat_status_tip_id', '=', 'bankomat_status_tips.id')
            ->where('bankomat_lokacijas.id', '=', $bankomat_lokacija_id)
            ->first();
        //dd($bankomat_lokacija_id, $bankomat_lokacija);
        return $bankomat_lokacija;
    }
    public static function multiSelectedTInfo($bankomat_lokacija_ids)
    {
        $bankomat_lokacija = BankomatLokacija::select( 
            'bankomat_lokacijas.id', 
            'bankomat_lokacijas.updated_at as last_updated', 
            'bankomats.id as bid', 
            'bankomats.b_sn',  
            'bankomats.b_terminal_id',
            'blokacijas.bl_naziv as blokacija_naziv',
            'blokacijas.bl_naziv_sufix as blokacija_naziv_sufix',
            'blokacijas.bl_adresa as blokacija_adresa',
            'blokacijas.bl_mesto as blokacija_mesto',
            'blokacija_tips.bl_tip_naziv as blokacija_tip_naziv',
            'blokacijas.pib',
            'blokacijas.mb',
            'blokacijas.email',
            'bankomat_tips.model',
            'bankomat_tips.proizvodjac',
            'bankomat_regions.r_naziv',
            'bankomat_status_tips.status_naziv',
            'bankomat_product_tips.bp_tip_naziv',
            )
            ->leftJoin('bankomats','bankomat_lokacijas.bankomat_id', '=', 'bankomats.id')
            ->leftJoin('blokacijas', 'bankomat_lokacijas.blokacija_id', '=', 'blokacijas.id')
            ->leftJoin('bankomat_tips', 'bankomats.bankomat_tip_id', '=', 'bankomat_tips.id')
            ->leftJoin('blokacija_tips', 'blokacijas.blokacija_tip_id', '=', 'blokacija_tips.id')
            ->leftJoin('bankomat_product_tips', 'bankomat_tips.bankomat_produkt_tip_id', '=', 'bankomat_product_tips.id')
            ->leftJoin('bankomat_regions', 'blokacijas.bankomat_region_id', '=', 'bankomat_regions.id')
            ->leftJoin('bankomat_status_tips', 'bankomat_lokacijas.bankomat_status_tip_id', '=', 'bankomat_status_tips.id')
            ->whereIn('bankomat_lokacijas.id', $bankomat_lokacija_ids)
            ->get();
       // dd($bankomat_lokacija);
        return $bankomat_lokacija;
    }
}