<?php
namespace App\Policies;

use App\Models\Booking;
use App\Models\User;

class BookingPolicy
{
    /**
     * Determine whether the user can view ANY bookings.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view-bookings')
        || $user->isSuperAdmin() || $user->isHallAdmin();
    }

    /**
     * Determine whether the user can view a specific booking.
     */
    public function view(User $user, Booking $booking): bool
    {
        // Super Admin can view all
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Has permission?
        if ($user->hasPermission('view-bookings')) {
            return true;
        }

        // If hall admin: only bookings in their hall
        if ($user->isHallAdmin() && $booking->hall_id == $user->hall_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create bookings.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create-bookings')
        || $user->isSuperAdmin();
    }

    /**
     * Determine whether the user can update/edit bookings.
     */
    public function update(User $user, Booking $booking): bool
    {
        // Super Admin can edit anything
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Normal permission
        if ($user->hasPermission('edit-bookings')) {
            return true;
        }

        // Hall admin can edit bookings belonging to their hall
        if ($user->isHallAdmin() && $booking->hall_id == $user->hall_id) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete bookings.
     */
    public function delete(User $user, Booking $booking): bool
    {
        // Super Admin can delete anything
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Normal permission
        if ($user->hasPermission('delete-bookings')) {
            return true;
        }

        // Hall admin can delete only their hall's bookings
        if ($user->isHallAdmin() && $booking->hall_id == $user->hall_id) {
            return true;
        }

        return false;
    }

    /**
     * Disable restoring and force deleting by default.
     */
    public function restore(User $user, Booking $booking): bool
    {
        return false;
    }

    public function forceDelete(User $user, Booking $booking): bool
    {
        return false;
    }
}
