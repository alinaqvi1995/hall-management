<?php
namespace App\Policies;

use App\Models\Hall;
use App\Models\User;

class HallPolicy
{
    /**
     * Only super admin can view list of all halls.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * View a single hall.
     * Super admin = can view all
     * Hall admin = only own hall
     */
    public function view(User $user, Hall $hall): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('hall_admin')) {
            return $user->hall_id === $hall->id;
        }

        return false;
    }

    /**
     * Only super admin can create halls.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Update hall:
     * - super admin: any hall
     * - hall admin: only their own hall
     */
    public function update(User $user, Hall $hall): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        if ($user->hasRole('hall_admin')) {
            return $user->hall_id === $hall->id;
        }

        return false;
    }

    /**
     * Only super admin can delete halls.
     */
    public function delete(User $user, Hall $hall): bool
    {
        return $user->hasRole('super_admin');
    }
}
