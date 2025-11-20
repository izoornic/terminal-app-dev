<?php

namespace App\Http\Livewire\Bankomati;

use Livewire\Component;
use Livewire\WithPagination;

use App\Models\Bankomat;
use App\Models\BankomatTip;

use Illuminate\Support\Facades\Config;

class BankomatTiopvi extends Component
{
    use WithPagination;

    public $modelId;

    public $newEditModalVisible;
    public $is_edit;
    public $deleteModalVisible;

    //NEW EDIT
    public $bankomat_model;
    public $bankomat_old_model;
    public $bankomat_proizvodjac;
    public $bankomat_product_tip;
    public $tip_uredjaja_naziv;
    public $opis;

    //DELETE
    public $can_not_delete;

    //SEARCH
    public $searchModel;
    public $searchProizvodjac;
    public $searchOpis;
    public $searchProductTip;

    /**
     * Listeners for Livewire events
     *
     * @var array
     */
    protected $listeners = ['newBankomatModel'];

    public function newBankomatModel()
    {
        $this->is_edit = false;
        $this->resetValidation();
        $this->resetInputFields();
        $this->newEditModalVisible = true;
    }

    public function saveNewBankomatModel()
    {
        //dd($this->modelData());
        $this->validate(
            [
                'bankomat_product_tip' => 'required|integer|exists:bankomat_product_tips,id',
                'bankomat_model' => 'required|string|max:64|unique:bankomat_tips,model',
                'bankomat_proizvodjac' => 'required|string|max:64',
                'opis' => 'nullable|string|max:255',
            ]
        );
        BankomatTip::create($this->modelData());
        $this->resetInputFields();
        $this->newEditModalVisible = false;
    }

    public function showUpdateModal($id)
    {
        $this->modelId = $id;
        $this->resetValidation();
        $this->resetInputFields();
        $this->is_edit = true;
        $this->loadModel();
        $this->newEditModalVisible = true;
    }

    public function updateBankomatModel()
    {
        //dd($this->bankomat_model);
        $this->validate(
            [
                'bankomat_product_tip' => 'required|integer|exists:bankomat_product_tips,id',
                'bankomat_model' =>($this->bankomat_model == $this->bankomat_old_model) ? '' : 'required|string|max:64|unique:bankomat_tips,model',
                'bankomat_proizvodjac' => 'required|string|max:64',
                'opis' => 'nullable|string|max:255',
            ]
        );
        $model = BankomatTip::find($this->modelId);
        $model->update($this->modelData());
        $this->resetInputFields();
        $this->newEditModalVisible = false;
    }

    public function loadModel()
    {
        $model = BankomatTip::find($this->modelId);
        $this->bankomat_product_tip = $model->bankomat_produkt_tip_id;
        $this->tip_uredjaja_naziv = $model->bankomat_product_tip()->first()->bp_tip_naziv;//->bp_tip_naziv;
        //dd( $this->tip_uredjaja_naziv);
        $this->bankomat_model = $model->model;
        $this->bankomat_old_model = $model->model;
        $this->bankomat_proizvodjac = $model->proizvodjac;
        $this->opis = $model->opis;
    }

    private function modelData()
    {
        return [
            'bankomat_produkt_tip_id' => $this->bankomat_product_tip,
            'model' => $this->bankomat_model,
            'proizvodjac' => $this->bankomat_proizvodjac,
            'opis' => $this->opis,
        ];
    }

    public function showDeleteModal($id)
    {
        $this->modelId = $id;
        $this->loadModel();
        $this->can_not_delete = Bankomat::where('bankomat_tip_id', '=', $id)->count();
        $this->deleteModalVisible = true;
    }

    public function deleteBankomatModel()
    {
        $model = BankomatTip::find($this->modelId);
        $model->delete();
        $this->deleteModalVisible = false;
    }

    private function resetInputFields()
    {
        $this->bankomat_product_tip = '';
        $this->bankomat_model = '';
        $this->bankomat_old_model = '';
        $this->bankomat_proizvodjac = '';
         $this->tip_uredjaja_naziv = '';
        $this->opis = '';
    }

    public function read()
    {
        return BankomatTip::select('bankomat_tips.*', 'bankomat_product_tips.bp_tip_naziv', 'bankomat_product_tips.id as produkt_tip_id')
            ->leftJoin('bankomat_product_tips', 'bankomat_product_tips.id', '=', 'bankomat_tips.bankomat_produkt_tip_id')
            ->when($this->searchModel, function ($query, $searchModel) {
                return $query->where('model', 'like', '%'.$searchModel.'%');
            })
            ->when($this->searchProizvodjac, function ($query, $searchProizvodjac) {
                return $query->where('proizvodjac', 'like', '%'.$searchProizvodjac.'%');
            })
            ->when($this->searchOpis, function ($query, $searchOpis) {
                return $query->where('opis', 'like', '%'.$this->searchOpis.'%');
            })
            ->when($this->searchProductTip, function ($query) {
                return $query->where('bankomat_produkt_tip_id', '=', $this->searchProductTip);
            })
            ->paginate(Config::get('global.paginate'));
    }

    public function render()
    {
        return view('livewire.bankomati.bankomat-tiopvi', [
            'data' => $this->read(),
        ]);
    }
}
