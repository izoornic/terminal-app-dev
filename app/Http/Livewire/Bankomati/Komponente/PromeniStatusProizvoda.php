<?php

namespace App\Http\Livewire\Bankomati\Komponente;

use Livewire\Component;
use App\Models\BankomatLokacija;
use App\Models\BankomatLocijaHirtory;
use Illuminate\Support\Facades\DB;

use App\Http\Helpers;

class PromeniStatusProizvoda extends Component
{
    public $modelId;
    public $bankomat_status;
    public $datum_promene;
    public $datum_promene_error;

    public function mount($bankomat_lokacija_id, $status)
    {
        $this->modelId = $bankomat_lokacija_id; //ovo je id terminal_lokacijas tabele
        $this->bankomat_status = $status;
        $this->datum_promene = date('Y-m-d');
        //$this->old_datum_promene =  BankomatLokacija::where('id', '=', $this->modelId)->first()->updated_at;
    }

    public function newStatus()
    {
        $this->validate(['datum_promene' => 'required|date']);

        $cuurent = BankomatLokacija::where('id', '=', $this->modelId)->first();

        if($cuurent->bankomat_status_tip_id == $this->bankomat_status) {
            $this->datum_promene_error = 'Niste promenili status.';
            return;
        }

        //Provera datuma koji je korisnik uneo za promenu statusa
        if(!$this->validDatumPromene([1, 2, 4])) return;
        
        //dd($this->datum_promene);
        DB::transaction(function()use($cuurent){
            $cuurent->update(['bankomat_status_tip_id' => $this->bankomat_status, 'updated_at' => $this->datum_promene]);

            BankomatLocijaHirtory::create([
                'bankomat_lokacija_id' => $this->modelId,
                'bankomat_id' => $cuurent['bankomat_id'],
                'blokacija_id' => $cuurent['blokacija_id'],
                'bankomat_status_tip_id' => $cuurent['bankomat_status_tip_id'],
                'user_id' => $cuurent['user_id'],
                'naplata' => $cuurent['naplata'],
                'updated_at' => $cuurent['updated_at'],
                'history_action_id' => 2
            ]);  
             
            
        });

         $this->emit('statusChanged');
    }

    private function validDatumPromene($history_a) 
    {
        $history_actions = $history_a ?? null;
        //Poslednja akcije koja se menja
        $last_change_model = BankomatLocijaHirtory::where('bankomat_lokacija_id', '=', $this->modelId)->whereIn('history_action_id', $history_actions)->orderBy('updated_at', 'desc')->first();

        //dd($history_actions,$last_change_model, $this->datum_promene);
        if(Helpers::equalGraterOrLessThan($last_change_model->updated_at->format('Y-m-d'), $this->datum_promene) == 'gt') {
            $this->datum_promene_error = 'Datum promene ne može biti manji od datuma poslednje promene.';
            return false;
        }

        $this->datum_promene .= ' ' . date('H:i:s');
        return true;
    }

    public function render()
    {
        return view('livewire.bankomati.komponente.promeni-status-proizvoda');
    }
}
