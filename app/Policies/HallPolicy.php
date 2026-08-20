<?php

namespace App\Policies;

use App\Models\Hall;
use App\Models\User;

class HallPolicy
{
    /**
     * Anyone with the view permission may open the hall list.
     *
     * The list itself is scoped by Hall::visibleTo(), so a hall admin sees only
     * their own venue. Requiring super_admin here previously made the whole
     * module unreachable for hall admins.
     */
    public function viewAny(User $user): bool
    {
        return $user->isSuperAdmin()
            || $user->isHallAdmin()
            || $user->hasPermission('view-halls');
    }

    public function view(User $user, Hall $hall): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Everyone else is confined to the hall on their own record.
        return (int) $user->hall_id === (int) $hall->id
            && ($user->isHallAdmin() || $user->hasPermission('view-halls'));
    }

    /** New venues are onboarded centrally. */
    public function create(User $user): bool
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Hall $hall): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return (int) $user->hall_id === (int) $hall->id
            && ($user->isHallAdmin() || $user->hasPermission('edit-halls'));
    }

    /** Deleting a venue removes its bookings and ledger, so it stays central. */
    public function delete(User $user, Hall $hall): bool
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Hall $hall): bool
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Hall $hall): bool
    {
        return false;
    }
}
