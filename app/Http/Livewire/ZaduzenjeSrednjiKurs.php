<?php

namespace App\Http\Livewire;

use App\Models\KursEvra;
use App\Models\LicencaDistributerTip;
use App\Models\LicencaDistributerMesec;

use Livewire\Component;
use Livewire\WithPagination;

class ZaduzenjeSrednjiKurs extends Component
{
    use WithPagination;

    //MOUNT
    public $did;
    public $mid;

    public $srednji_kurs;
    public $dist_name;

    public $kurs_evra;

    /**
     * mount
     *
     * @return void
     */
    public function mount()
    {
        $this->did = request()->query('id');
        $this->mid = request()->query('mid');
        $this->dist_name = LicencaDistributerTip::DistributerName($this->did);
        $this->kurs_evra = KursEvra::latest()->first();
        $this->srednji_kurs = round(floatval($this->kurs_evra->srednji_kurs), 2);
    }

    /**
     * The validation rules
     *
     * @return void
     */
    public function rules()
    {
        return [  
            'srednji_kurs' => ['required', 'numeric']
        ];
    }

    public function nextStep()
    {
        $this->validate();
        return redirect('/zaduzenje-distributer-mesec?id='.$this->did.'&mid='.$this->mid.'&sk='.$this->srednji_kurs);
    }

    public function render()
    {
        return view('livewire.zaduzenje-srednji-kurs');
    }
}