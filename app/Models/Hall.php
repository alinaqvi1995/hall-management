<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hall extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    public const HALL_TYPES = [
        'marquee' => 'Marquee',
        'banquet' => 'Banquet Hall',
        'lawn' => 'Open Lawn',
        'farmhouse' => 'Farmhouse',
        'hotel' => 'Hotel Hall',
        'community' => 'Community Centre',
    ];

    protected $fillable = [
        'name',
        'logo',
        'owner_name',
        'phone',
        'email',
        'address',
        'state_id',
        'city_id',
        'city',
        'state',
        'country',
        'zipcode',
        'area',
        'description',
        'hall_capacity',
        'default_per_head_rate',
        'advance_policy_percent',
        'cancellation_charge_percent',
        'tax_percent',
        'hall_types',
        'registration_number',
        'ntn_number',
        'gst_number',
        'established_at',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'established_at' => 'date',
        'status' => 'boolean',
        'hall_capacity' => 'integer',
        'default_per_head_rate' => 'decimal:2',
        'advance_policy_percent' => 'integer',
        'cancellation_charge_percent' => 'integer',
        'tax_percent' => 'decimal:2',
    ];

    /* ---------------------------------------------------------------- relations */

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function lawns()
    {
        return $this->hasMany(Lawn::class, 'hall_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    public function addons()
    {
        return $this->hasMany(Addon::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function staff()
    {
        return $this->hasMany(Staff::class);
    }

    public function vendors()
    {
        return $this->hasMany(Vendor::class);
    }

    public function stateRelation()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function cityRelation()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /* ------------------------------------------------------------------- scopes */

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Restrict to the halls the given user may see. Mirrors BelongsToHall but
     * keys on the primary key, since a hall has no hall_id of its own.
     */
    public function scopeVisibleTo($query, $user = null)
    {
        $user ??= auth()->user();

        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->isSuperAdmin()) {
            return $query;
        }

        if (! $user->hall_id) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('id', $user->hall_id);
    }

    /* --------------------------------------------------------------- accessors */

    public function getStatusLabelAttribute(): string
    {
        return $this->status
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-danger">Inactive</span>';
    }

    public function getHallTypeLabelAttribute(): ?string
    {
        if (! $this->hall_types) {
            return null;
        }

        return self::HALL_TYPES[$this->hall_types] ?? ucfirst($this->hall_types);
    }

    public function getCreatorNameAttribute(): ?string
    {
        return $this->creator?->name;
    }

    public function getEditorNameAttribute(): ?string
    {
        return $this->updater?->name;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset($this->logo) : null;
    }

    /** Full postal address, skipping the parts that were never filled in. */
    public function getFullAddressAttribute(): string
    {
        $parts = array_filter([
            $this->address,
            $this->cityRelation?->name ?: $this->city,
            $this->stateRelation?->name ?: $this->state,
            $this->country,
            $this->zipcode,
        ]);

        return $parts ? implode(', ', $parts) : '-';
    }

    public function getEstablishedAtFormattedAttribute(): string
    {
        return $this->established_at ? $this->established_at->format('d M Y') : '-';
    }

    public function getCreatedAtFormattedAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('d M Y h:i A') : '-';
    }

    public function getUpdatedAtFormattedAttribute(): string
    {
        return $this->updated_at ? $this->updated_at->format('d M Y h:i A') : '-';
    }
}
