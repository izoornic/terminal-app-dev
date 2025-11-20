<?php

namespace App\Http\Livewire\Bankomati\Komponente;

use Livewire\Component;
use App\Models\BlokacijaKontaktOsoba;

class KontaktOsobe extends Component
{
    public $b_lokacija_id;

    public $kontakt_name;
    public $kontakt_tel;
    public $kontakt_email;

    public $newKontaktOsobaVisible = false;
    
    public function mount($b_lokacija_id)
    {
        $this->b_lokacija_id = $b_lokacija_id;
    }

    public function addKontaktOsobuVisible()
    {
        $this->resetValidation();
        $this->kontakt_name = '';
        $this->kontakt_tel = '';
        $this->kontakt_email = '';
        $this->newKontaktOsobaVisible = true;
    }

    public function addKontaktOsobu()
    {
        $this->validate(
            [
                'kontakt_name' => 'required|string|min:3|max:255',
                //'kontakt_tel' => 'nullable|phone:INTERNATIONAL',
                'kontakt_email' => 'nullable|email|max:255'
            ]
        );
        $kontakt_osoba = new BlokacijaKontaktOsoba();
        $kontakt_osoba->ime = $this->kontakt_name;
        $kontakt_osoba->telefon = $this->kontakt_tel ?: null;
        $kontakt_osoba->email = $this->kontakt_email ?: null;
        $kontakt_osoba->blokacija_id = $this->b_lokacija_id;
        $kontakt_osoba->save();
        $this->newKontaktOsobaVisible = false;
    }  

    public function removeKontaktOsobu($id)
    {
        $kontakt_osoba = BlokacijaKontaktOsoba::find($id);
        $kontakt_osoba->delete();
    }

    public function read()
    {
        return BlokacijaKontaktOsoba::where('blokacija_id', '=', $this->b_lokacija_id)->get();
    }
    
    public function render()
    {
        return view('livewire.bankomati.komponente.kontakt-osobe', [
            'data' => $this->read()
        ]);
    }
}
