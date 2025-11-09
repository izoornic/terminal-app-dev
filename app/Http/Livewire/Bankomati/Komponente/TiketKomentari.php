<?php

namespace App\Http\Livewire\Bankomati\Komponente;

use Livewire\Component;

use App\Models\BankomatTiketKomantar;
use App\Models\BankomatTiket;

class TiketKomentari extends Component
{
    public $tiketid;
    public $tiket_status;
    public $user_id;
    //public $komentari;
    public $novi_komentar;

    public function mount($tiketid, $tiket_status)
    {
        $this->tiketid = $tiketid;
        $this->tiket_status = $tiket_status;
        $this->user_id = auth()->user()->id;

        //$komentari = BankomatTiketKomantar::where('bankomat_tiket_id', '=', $this->tiketid)->get();
        //dd($komentari);
    }

    public function posaljiKomentar()
    {
        $this->validate([
            'novi_komentar' => 'required|min:3|max:1000',
        ]);

        BankomatTiketKomantar::create([
            'bankomat_tiket_id' => $this->tiketid,
            'komentar' => $this->novi_komentar,
            'user_id' => auth()->user()->id,
        ]);

        $this->novi_komentar = '';

        $this->updateNoOfComments(5);
    }

    public function deleteKomentar($id)
    {
        BankomatTiketKomantar::where('id', '=', $id)->delete();
        $this->updateNoOfComments(6);
    }

    private function updateNoOfComments($history_action_id)
    {
        $komentari = BankomatTiketKomantar::where('bankomat_tiket_id', '=', $this->tiketid)->get();
        $tiket = BankomatTiket::where('id', '=', $this->tiketid)->first();
        $tiket->br_komentara = $komentari->count();
        $tiket->save();
        $tiket->newHistroy($history_action_id);
    }

    public function read()
    {
        return BankomatTiketKomantar::select('bankomat_tiket_komantars.*', 'users.name', 'users.id as uid')
                                        ->where('bankomat_tiket_id', '=', $this->tiketid)
                                        ->leftJoin('users', 'bankomat_tiket_komantars.user_id', '=', 'users.id')
                                        ->get();
        
    }

    public function render()
    {
        return view('livewire.bankomati.komponente.tiket-komentari', [
            'komentari' => $this->read(),
        ]);
    }
}
