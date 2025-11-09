<?php

namespace App\Http\Livewire\Bankomati\Komponente;

use Livewire\Component;
use App\Actions\Bankomati\BankomatiReadActions;
use Illuminate\Support\Facades\Config;

class IzborProizvoda extends Component
{
    //USER ROLE
    public $role_region;
    public $proizvod_model_tip;

    public $searchSN;
    public $searchPLokacijaNaziv;
    public $searchPlokacijaMesto;
    public $searchPlokacijaRegion;

    public function mount()
    {
        $this->proizvod_model_tip = null;
        $this->role_region =auth()->user()->userBankmatPositionAndRegion();
        if($this->role_region['role'] != 'admin') {
            $this->searchPlokacijaRegion = $this->role_region['region'];
        }
    }

    public function izabraniProizvod($id)
    {
        $this->emit('izabraniProizvod', $id);
    }

    public function read()
    {
        if($this->proizvod_model_tip == null) return [];
        $search = [
            'product_tip' => $this->proizvod_model_tip,
            'b_sn' => $this->searchSN,
            'lokacija_naziv' => $this->searchPLokacijaNaziv,
            'mesto' => $this->searchPlokacijaMesto,
            'region' => $this->searchPlokacijaRegion
        ];
        $builder = BankomatiReadActions::BankomatiRead($search);
        // paginate the builder
        $perPage = Config::get('global.terminal_paginate');
        $terms = $builder->paginate($perPage, ['*'], 'proizvodi');
        return $terms;
    }
    public function render()
    {
        return view('livewire.bankomati.komponente.izbor-proizvoda', [
            'proizvodi' => $this->read(),
        ]);
    }
}
