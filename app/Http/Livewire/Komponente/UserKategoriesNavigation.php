<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use App\Models\PozicijaKategoriy;

class UserKategoriesNavigation extends Component
{
    public $selectedKatId = null;
    public function mount()
    {
        $this->selectedKatId = PozicijaKategoriy::orderBy('menu_order')->first()->id;
        $this->emit('izaberiKategoriju', $this->selectedKatId);
       //dd(PozicijaKategoriy::orderBy('menu_order')->get());
    }

    protected $listeners = ['izaberiKategoriju' => 'postaviKategoriju'];
    public function postaviKategoriju($katId)
    {
        $this->selectedKatId = $katId;
    }

    public function render()
    {
        return view('livewire.komponente.user-kategories-navigation');
    }
}
