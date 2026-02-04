<?php

// app/Policies/PartReservationPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\PartReservation;
use Illuminate\Auth\Access\HandlesAuthorization;

class PartReservationPolicy
{
    use HandlesAuthorization;

    public function view(User $user, PartReservation $reservation): bool
    {
        // Admin i Šef servisa mogu videti sve
        if ($user->hasPermissionTo('parts.reservation.manage')) {
            return true;
        }

        // Serviser može videti samo svoje rezervacije
        return $reservation->korisnik_id === $user->id;
    }

    public function fulfill(User $user, PartReservation $reservation): bool
    {
        // Može samo vlasnik ili neko sa manage pravom
        return $user->hasPermissionTo('parts.reservation.manage') 
            || $reservation->korisnik_id === $user->id;
    }

    public function cancel(User $user, PartReservation $reservation): bool
    {
        // Može samo vlasnik ili neko sa manage pravom
        return $user->hasPermissionTo('parts.reservation.manage') 
            || $reservation->korisnik_id === $user->id;
    }
}