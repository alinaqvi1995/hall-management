<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Determine if the user can view another user.
     */
    public function view(User $currentUser, User $userToView)
    {
        return $this->update($currentUser, $userToView);
    }

    /**
     * Determine if the user can create a new user.
     */
    public function create(User $currentUser)
    {
        // Only super admins can create users
        return $currentUser->hasRole('super_admin');
    }

    /**
     * Determine if the user can update another user.
     */
    public function update(User $currentUser, User $userToEdit)
    {
        // Super admins can edit anyone
        if ($currentUser->hasRole('super_admin')) {
            return true;
        }

        // Hall admins can only edit users in their hall
        if ($currentUser->hasRole('hall_admin')) {
            return $currentUser->hall_id === $userToEdit->hall_id;
        }

        // Regular users cannot edit anyone
        return false;
    }

    /**
     * Determine if the user can delete another user.
     */
    public function delete(User $currentUser, User $userToDelete)
    {
        // Only super admins can delete users
        return $currentUser->hasRole('super_admin');
    }
}
