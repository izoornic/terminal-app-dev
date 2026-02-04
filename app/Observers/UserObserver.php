<?php

namespace App\Observers;

use App\Models\User;
use App\Models\PozicijaTip;

class UserObserver
{
    public function created(User $user)
    {
        $this->syncRoleFromPozicija($user);
    }

    public function updated(User $user)
    {
        if ($user->isDirty('pozicija_tipId')) {
            $this->syncRoleFromPozicija($user);
        }
    }

    private function syncRoleFromPozicija(User $user)
    {
        if ($user->pozicija_tipId) {
            $pozicija = PozicijaTip::find($user->pozicija_tipId);
            
            if ($pozicija) {
                // Sync role (removes all other roles and assigns this one)
                $user->syncRoles([$pozicija->naziv]);
            }
        }
    }
}