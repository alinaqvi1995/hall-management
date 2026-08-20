<?php

namespace App\Traits;

use App\Models\Hall;
use Illuminate\Support\Facades\Auth;

/**
 * Controller-side helpers for working out which hall the request is about.
 *
 * A super admin may work across halls and optionally filter with ?hall_id=,
 * while everyone else is pinned to the hall on their user record.
 */
trait ResolvesCurrentHall
{
    /**
     * The hall id to scope queries by, or null to mean "all halls".
     * Only a super admin can ever receive null.
     */
    protected function scopedHallId(): ?int
    {
        $user = Auth::user();

        if (! $user) {
            return 0;
        }

        if (! $user->isSuperAdmin()) {
            // 0 matches nothing, which is the safe reading of "no hall attached".
            return $user->hall_id ?: 0;
        }

        $requested = request()->integer('hall_id');

        return $requested > 0 ? $requested : null;
    }

    /**
     * The hall a write should be attached to. Falls back to the user's own hall
     * and refuses a hall the user has no business writing to.
     */
    protected function hallIdForWrite($requestedHallId = null): int
    {
        $user = Auth::user();

        if (! $user->isSuperAdmin()) {
            abort_unless($user->hall_id, 403, 'Your account is not linked to a hall.');

            return (int) $user->hall_id;
        }

        abort_unless($requestedHallId, 422, 'A hall must be selected.');

        return (int) $requestedHallId;
    }

    /** Halls the current user may pick from in a form. */
    protected function selectableHalls()
    {
        return Hall::visibleTo()->active()->orderBy('name')->get(['id', 'name']);
    }

    /** Block access to a record that belongs to another hall. */
    protected function authorizeHallAccess($model): void
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return;
        }

        $hallId = $model instanceof Hall ? $model->id : $model->hall_id;

        abort_unless($hallId && (int) $hallId === (int) $user->hall_id, 403, 'Unauthorized');
    }
}
