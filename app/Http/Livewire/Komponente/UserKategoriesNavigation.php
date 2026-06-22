<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use App\Models\PozicijaKategoriy;

class UserKategoriesNavigation extends Component
{
    public $selectedKatId = null;
    public function mount(): void
    {
        $this->selectedKatId = PozicijaKategoriy::orderBy('menu_order')->first()->id;
    }

    public function selectKategorija(int $katId): void
    {
        $this->selectedKatId = $katId;
        $this->dispatch('izaberiKategoriju', katId: $katId);
    }

    public function render()
    {
        return view('livewire.komponente.user-kategories-navigation');
    }
}
