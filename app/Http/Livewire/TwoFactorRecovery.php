<?php

namespace App\Http\Livewire;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Config;
use Laravel\Fortify\Actions\GenerateNewRecoveryCodes;
use Livewire\Component;
use Livewire\WithPagination;

class TwoFactorRecovery extends Component
{
    use WithPagination;

    /**
     * Only this hardcoded user (admin, id = 1) may use this page.
     *
     * @var int
     */
    public const ADMIN_USER_ID = 1;

    public $search;

    public $modelId;
    public $userName;

    public $confirmingGenerationVisible = false;

    /**
     * The recovery codes generated in the last action.
     *
     * @var array<int, string>
     */
    public $recoveryCodes = [];

    public $generatedForName;

    public $actionMessage;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Users that currently have two factor authentication enabled.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function read()
    {
        return User::query()
            ->whereNotNull('two_factor_secret')
            ->where('pozicija_tipId', '!=', 7)
            ->when($this->search, function ($query) {
                $query->where(function ($sub) {
                    $sub->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(Config::get('global.paginate'));
    }

    /**
     * Shows the confirmation modal before regenerating codes.
     *
     * @param  mixed  $id
     * @return void
     */
    public function confirmGenerateShowModal($id): void
    {
        $this->resetGenerated();
        $this->modelId = $id;
        $this->userName = User::findOrFail($id)->name;
        $this->confirmingGenerationVisible = true;
    }

    /**
     * Regenerates the two factor recovery codes for the selected user.
     *
     * @return void
     */
    public function generateRecoveryCodes(GenerateNewRecoveryCodes $generator): void
    {
        abort_unless((int) auth()->id() === self::ADMIN_USER_ID, 403, 'Unauthorized action.');

        $user = User::findOrFail($this->modelId);

        if (! $user->hasEnabledTwoFactorAuthentication()) {
            $this->confirmingGenerationVisible = false;
            $this->actionMessage = 'Korisnik nema uključenu 2FA zaštitu.';

            return;
        }

        $generator($user);

        $user = $user->fresh();

        $this->recoveryCodes = $user->recoveryCodes();
        $this->generatedForName = $user->name;
        $this->actionMessage = null;
        $this->confirmingGenerationVisible = false;
    }

    private function resetGenerated(): void
    {
        $this->recoveryCodes = [];
        $this->generatedForName = null;
        $this->actionMessage = null;
    }

    public function render(): View
    {
        return view('livewire.two-factor-recovery', [
            'data' => $this->read(),
        ]);
    }
}
