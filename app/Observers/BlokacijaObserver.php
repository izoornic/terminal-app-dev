<?php

namespace App\Observers;

use App\Models\Blokacija;

class BlokacijaObserver
{
    /**
     * Handle the Blokacija "created" event.
     *
     * @param  \App\Models\Blokacija  $blokacija
     * @return void
     */
    public function created(Blokacija $blokacija)
    {
        // cuva sopstveni id u polju parent_id kada je glavna lokacija
        if($blokacija->is_duplicate == 0){
            $blokacija->parent_id = $blokacija->id;
            $blokacija->save();
        }
    }

    /**
     * Handle the Blokacija "updated" event.
     *
     * @param  \App\Models\Blokacija  $blokacija
     * @return void
     */
    public function updated(Blokacija $blokacija)
    {
        //
    }

    /**
     * Handle the Blokacija "deleted" event.
     *
     * @param  \App\Models\Blokacija  $blokacija
     * @return void
     */
    public function deleted(Blokacija $blokacija)
    {
        //
    }

    /**
     * Handle the Blokacija "restored" event.
     *
     * @param  \App\Models\Blokacija  $blokacija
     * @return void
     */
    public function restored(Blokacija $blokacija)
    {
        //
    }

    /**
     * Handle the Blokacija "force deleted" event.
     *
     * @param  \App\Models\Blokacija  $blokacija
     * @return void
     */
    public function forceDeleted(Blokacija $blokacija)
    {
        //
    }
}
