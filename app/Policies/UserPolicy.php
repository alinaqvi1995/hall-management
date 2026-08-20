<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $currentUser): bool
    {
        return $currentUser->isSuperAdmin() || $currentUser->hasPermission('view-users');
    }

    public function view(User $currentUser, User $userToView): bool
    {
        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        if (! $currentUser->hasPermission('view-users')) {
            return false;
        }

        // Everyone else is confined to colleagues at their own hall.
        return $currentUser->hall_id !== null
            && (int) $currentUser->hall_id === (int) $userToView->hall_id;
    }

    /**
     * A hall admin needs to be able to onboard their own staff, so this follows
     * the `create-users` permission rather than requiring super_admin. The
     * controller pins the new user to the creator's hall and blocks privilege
     * escalation.
     */
    public function create(User $currentUser): bool
    {
        return $currentUser->isSuperAdmin() || $currentUser->hasPermission('create-users');
    }

    public function update(User $currentUser, User $userToEdit): bool
    {
        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        if (! $currentUser->hasPermission('edit-users')) {
            return false;
        }

        // Nobody may edit a super admin except another super admin.
        if ($userToEdit->isSuperAdmin()) {
            return false;
        }

        return $currentUser->hall_id !== null
            && (int) $currentUser->hall_id === (int) $userToEdit->hall_id;
    }

    public function delete(User $currentUser, User $userToDelete): bool
    {
        // Deleting your own account from the user list would lock you out.
        if ($currentUser->id === $userToDelete->id) {
            return false;
        }

        if ($currentUser->isSuperAdmin()) {
            return true;
        }

        if (! $currentUser->hasPermission('delete-users') || $userToDelete->isSuperAdmin()) {
            return false;
        }

        return $currentUser->hall_id !== null
            && (int) $currentUser->hall_id === (int) $userToDelete->hall_id;
    }
}
