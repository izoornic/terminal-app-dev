<?php

namespace App\Http\Livewire\Distributer;

use App\Models\TerminalCampagin;
use App\Models\LicencaDistributerTip;

use Livewire\Component;
use Livewire\WithPagination;

use Illuminate\Support\Facades\Config;

class DistCampagin extends Component
{
    use WithPagination;

    protected $listeners = ['newCampagin'];

    public $modalFormVisible;
    public $modalConfirmDeleteVisible;
    public $modelId;

    public $is_update;

    // create / update
    public $campagin_name;
    public $campagin_description;

    public function rules()
    {
        return [
            'campagin_name' => 'required',
        ];
    }

    public function newCampagin()
    {
        $this->createShowModal();
    }

    public function loadModel()
    {
        $data = TerminalCampagin::find($this->modelId);
        $this->campagin_name        = $data->campagin_name;
        $this->campagin_description = $data->campagin_description;
    }

    public function modelData()
    {
        return [
            'distributer_id'       => LicencaDistributerTip::distributerIdByUserId(auth()->user()->id),
            'campagin_name'        => $this->campagin_name,
            'campagin_description' => $this->campagin_description,
        ];
    }

    public function createShowModal()
    {
        $this->resetValidation();
        $this->reset();
        $this->is_update       = false;
        $this->modalFormVisible = true;
    }

    public function create()
    {
        $this->validate();
        TerminalCampagin::create($this->modelData());
        $this->modalFormVisible = false;
        $this->reset();
    }

    public function updateShowModal($id)
    {
        $this->resetValidation();
        $this->reset();
        $this->is_update       = true;
        $this->modelId         = $id;
        $this->modalFormVisible = true;
        $this->loadModel();
    }

    public function update()
    {
        $this->validate();
        TerminalCampagin::find($this->modelId)->update([
            'campagin_name'        => $this->campagin_name,
            'campagin_description' => $this->campagin_description,
        ]);
        $this->modalFormVisible = false;
    }

    public function toggleActive($id)
    {
        $campaign = TerminalCampagin::find($id);
        $campaign->update(['active' => !$campaign->active]);
    }

    public function deleteShowModal($id)
    {
        $this->modelId = $id;
        $this->loadModel();
        $this->modalConfirmDeleteVisible = true;
    }

    public function delete()
    {
        TerminalCampagin::find($this->modelId)->delete();
        $this->modalConfirmDeleteVisible = false;
        $this->resetPage();
    }

    public function read()
    {
        $distributerId = LicencaDistributerTip::distributerIdByUserId(auth()->user()->id);

        return TerminalCampagin::where('distributer_id', $distributerId)
            ->orderBy('active', 'desc')
            ->orderBy('campagin_name')
            ->paginate(Config::get('global.paginate'));
    }

    public function render()
    {
        return view('livewire.distributer.dist-campagin', [
            'data' => $this->read(),
        ]);
    }
}
