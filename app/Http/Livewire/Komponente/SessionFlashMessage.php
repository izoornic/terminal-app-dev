<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;

class SessionFlashMessage extends Component
{
    public $status;
    public $error;

    /**
     * Listeners for Livewire events.
     *
     * @var array
     */
    protected $listeners = ['flashMessage', 'fleshError'];

    public function flashMessage($message)
    {
         $this->status = $message;
    }

    public function fleshError($message)
    {
        $this->error = $message;
    }

    public function closeAlert()
    {
        $this->status = null;
        $this->error = null;
    }

    public function render()
    {
        return view('livewire.komponente.session-flash-message');
    }
}
