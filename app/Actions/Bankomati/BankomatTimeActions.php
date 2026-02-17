<?php

namespace App\Actions\Bankomati;
use App\Models\BankomatLocijaHirtory;

class BankomatTimeActions
{
    public static function compareTicketTime($bankomat_lokacija_id, $datum_promene, $vreme_promene, $history_action = 'open')
    {
        //akcija je otvaranje tiketa - 9 ili zatvaranje tiketa - 8
        if($history_action == 'open') $history_action = 9;
        if($history_action == 'close') $history_action = 8;
        //poslednji datum zatvaranja tketa za bankomat to je history action 9
        $last_change_model = BankomatLocijaHirtory::where('bankomat_lokacija_id', '=', $bankomat_lokacija_id)->where('history_action_id', '=', $history_action)->orderBy('created_at', 'desc')->first();
        //dd($last_change_model);
        if($last_change_model){
            $poslednja_promena = $last_change_model->created_at;
            $datuma_promene_p = $datum_promene . ' ' . $vreme_promene;

            if($poslednja_promena->format('Y-m-d H:i:s') > $datuma_promene_p){
                return false;
            }else{
                return true;
            }

        }else{
            return true;
        }
    }
}