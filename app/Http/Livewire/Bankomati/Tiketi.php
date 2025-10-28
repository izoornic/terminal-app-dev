<?php

namespace App\Http\Livewire\Bankomati;

use Livewire\Component;
use Illuminate\Support\Facades\Config;
use App\Actions\Bankomati\BankomatTiketReadActions;

class Tiketi extends Component
{

    public $searchProductTip;
    public $searchStatus;
    public $searchLokacijaNaziv;
    public $searchMesto;

    public $newTicketShowModal = false;
    public $bankomat_lokacija_id;
    
    /**
     * Listeners for Livewire events
     *
     * @var array
     */
    protected $listeners = ['newTicket', 'izabraniProizvod', 'newTicketCreated'];

    public function newTicket()
    {
        $this->bankomat_lokacija_id = null;
        $this->newTicketShowModal = true;
    }

    public function izabraniProizvod($id)
    {
        $this->bankomat_lokacija_id = $id;
        //dd($this->bankomat_lokacija_id);
    }

    public function newTicketCreated($id)
    {
        $this->newTicketShowModal = false;
        $this->emit('flashMessage', 'Tiket #'.$id.' je uspešno dodat.');
    }

    public function read()
    {
        $search = [
            'searchProductTip' => $this->searchProductTip,
            'searchStatus' => $this->searchStatus,
            'searchLokacijaNaziv' => $this->searchLokacijaNaziv,
            'searchMesto' => $this->searchMesto
        ];
        $builder = BankomatTiketReadActions::read($search);
        // paginate the builder
        $perPage = Config::get('global.terminal_paginate');
        $terms = $builder->paginate($perPage, ['*'], 'tiketi');
        return $terms;
    }
    public function render()
    {
        return view('livewire.bankomati.tiketi', [
            'data' => $this->read(),
        ]);
    }
}
