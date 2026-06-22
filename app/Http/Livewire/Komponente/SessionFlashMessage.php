<?php

namespace App\Http\Livewire\Komponente;

use Livewire\Component;
use Livewire\Attributes\On;

class SessionFlashMessage extends Component
{
    public $status;
    public $error;

    #[On('flashMessage')]
    public function flashMessage($message)
    {
         $this->status = $message;
    }

    #[On('fleshError')]
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
