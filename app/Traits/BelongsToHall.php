<?php

namespace App\Traits;

use App\Models\Hall;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Shared hall-tenancy behaviour.
 *
 * Every operational record (booking, payment, expense, package, staff...)
 * belongs to exactly one hall. Anyone who is not a super admin may only ever
 * see rows for the hall they are attached to, so scoping lives here rather
 * than being re-implemented in each controller.
 */
trait BelongsToHall
{
    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class);
    }

    /**
     * Restrict a query to the halls the given user is allowed to see.
     * A super admin sees everything; anybody else sees their own hall only,
     * and a user with no hall attached sees nothing at all.
     */
    public function scopeVisibleTo(Builder $query, $user = null): Builder
    {
        $user ??= Auth::user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if (! $user->hall_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where($this->getTable().'.hall_id', $user->hall_id);
    }

    public function scopeForHall(Builder $query, $hallId): Builder
    {
        return $query->where($this->getTable().'.hall_id', $hallId);
    }
}
