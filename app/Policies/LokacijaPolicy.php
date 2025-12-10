<?php

// app/Policies/LokacijaPolicy.php (dodatak za existing policy)
namespace App\Policies;

use App\Models\User;
use App\Models\Lokacija;
use Illuminate\Auth\Access\HandlesAuthorization;

class LokacijaPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Lokacija $location): bool
    {
        // Admin može sve
        if ($user->hasPermissionTo('parts.stock.view.all')) {
            return true;
        }
        //TODO: Pretpostavka: User model ima assignedLocations relationship
        // Serviser može samo svoju lokaciju
        // Pretpostavka: User model ima assignedLocations relationship
        return $user->assignedLocations->contains($location->id);
    }
}