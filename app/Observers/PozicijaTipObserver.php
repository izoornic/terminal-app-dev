<?php

namespace App\Observers;

use App\Models\PozicijaTip;
use Spatie\Permission\Models\Role;


class PozicijaTipObserver
{
    /**
     * Handle the PozicijaTip "created" event.
     *
     * @param  \App\Models\PozicijaTip  $pozicijaTip
     * @return void
     */
    public function created(PozicijaTip $pozicijaTip)
    {
        // Create corresponding Spatie role
        Role::firstOrCreate([
            'name' => $pozicijaTip->naziv,
            'guard_name' => 'web'
        ]);
    }

    /**
     * Handle the PozicijaTip "updated" event.
     *
     * @param  \App\Models\PozicijaTip  $pozicijaTip
     * @return void
     */
    public function updated(PozicijaTip $pozicijaTip)
    {
        // Update Spatie role name
        $role = Role::where('name', $pozicijaTip->getOriginal('naziv'))->first();
        
        if ($role) {
            $role->update(['name' => $pozicijaTip->naziv]);
            
            // Re-sync all users with this pozicija
            $pozicijaTip->users()->each(function ($user) use ($pozicijaTip) {
                $user->syncRoles([$pozicijaTip->naziv]);
            });
        }
    }

    /**
     * Handle the PozicijaTip "deleted" event.
     *
     * @param  \App\Models\PozicijaTip  $pozicijaTip
     * @return void
     */
    public function deleted(PozicijaTip $pozicijaTip)
    {
        // Optionally remove the Spatie role
        Role::where('name', $pozicijaTip->naziv)->delete();
    }

    /**
     * Handle the PozicijaTip "restored" event.
     *
     * @param  \App\Models\PozicijaTip  $pozicijaTip
     * @return void
     */
    public function restored(PozicijaTip $pozicijaTip)
    {
        //
    }

    /**
     * Handle the PozicijaTip "force deleted" event.
     *
     * @param  \App\Models\PozicijaTip  $pozicijaTip
     * @return void
     */
    public function forceDeleted(PozicijaTip $pozicijaTip)
    {
        //
    }
}
