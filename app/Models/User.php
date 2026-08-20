<?php

namespace App\Models;

use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, LogsActivity, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'hall_id',
        'force_logout',
        'otp_code',
        'otp_expires_at',
        'otp_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Model-level defaults matching the column defaults, so a newly created
     * user is active on the returned instance and not only after a refresh.
     */
    protected $attributes = [
        'is_active' => true,
        'force_logout' => false,
    ];

    /**
     * Per-instance caches for role and permission lookups.
     *
     * Every `@can` in a Blade view runs through Gate::before -> hasRole(), and
     * a list page can render hundreds of them. Without these the roles table
     * was queried once per check — several hundred queries on a long page.
     *
     * @var array<string, bool>|null
     */
    private ?array $roleCache = null;

    /** @var array<int, string>|null */
    private ?array $permissionCache = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'force_logout' => 'boolean',
        ];
    }

    public function isActive(): bool
    {
        return (bool) $this->is_active;
    }

    public function isForcedLogout(): bool
    {
        return (bool) $this->force_logout;
    }

    public function getStatusFormattedAttribute(): string
    {
        return $this->is_active ? 'Active' : 'Inactive';
    }

    public function getStatusLabelAttribute()
    {
        return $this->is_active
            ? '<span class="badge bg-success">Active</span>'
            : '<span class="badge bg-danger">Inactive</span>';
    }

    /** Relationships */
    public function hall()
    {
        return $this->belongsTo(Hall::class, 'hall_id');
    }

    public function detail()
    {
        return $this->hasOne(UserDetail::class);
    }

    public function referredUsers()
    {
        return $this->hasMany(UserDetail::class, 'referred_by');
    }

    public function referredBy()
    {
        return $this->hasOneThrough(
            User::class,
            UserDetail::class,
            'user_id',
            'id',
            'id',
            'referred_by'
        );
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_user');
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole('super_admin');
    }

    public function isHallAdmin(): bool
    {
        return $this->hasRole('hall_admin');
    }

    /**
     * Role slugs held by this user, loaded once per instance.
     *
     * @return array<int, string>
     */
    public function roleSlugs(): array
    {
        if ($this->roleCache === null) {
            $this->roleCache = $this->relationLoaded('roles')
                ? $this->roles->pluck('slug')->all()
                : $this->roles()->pluck('slug')->all();
        }

        return $this->roleCache;
    }

    public function directPermissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    /** Role + Permission Helpers */

    // permissions via roles
    public function permissions()
    {
        return \App\Models\Permission::query()
            ->select('permissions.*')
            ->join('permission_role', 'permissions.id', '=', 'permission_role.permission_id')
            ->join('role_user', 'permission_role.role_id', '=', 'role_user.role_id')
            ->where('role_user.user_id', $this->id)
            ->distinct();
    }

    public function listPermissions(): array
    {
        return [
            'roles' => $this->roles()->pluck('slug')->all(),
            'role_permissions' => $this->permissions()->pluck('slug')->all(),
            'direct_permissions' => $this->directPermissions()->pluck('slug')->all(),
            'all_permissions' => $this->allPermissions()->pluck('slug')->all(),
        ];
    }

    // Merge role-based and direct permissions
    public function allPermissions()
    {
        $rolePermissions = $this->permissions()->get();
        $directPermissions = $this->directPermissions()->get();

        $all = $rolePermissions
            ->merge($directPermissions)
            ->unique('id')
            ->values();

        return $all;
    }

    /**
     * Permission slugs from roles plus direct grants, loaded once per instance.
     *
     * @return array<int, string>
     */
    public function permissionSlugs(): array
    {
        if ($this->permissionCache === null) {
            $this->permissionCache = $this->allPermissions()->pluck('slug')->unique()->all();
        }

        return $this->permissionCache;
    }

    public function hasPermission(string $slug): bool
    {
        return in_array($slug, $this->permissionSlugs(), true);
    }

    public function hasAllPermissions(array $slugs): bool
    {
        $all = $this->permissionSlugs();

        foreach ($slugs as $slug) {
            if (! in_array($slug, $all, true)) {
                return false;
            }
        }

        return true;
    }

    public function hasAnyPermission(array $slugs): bool
    {
        $all = $this->permissionSlugs();

        foreach ($slugs as $slug) {
            if (in_array($slug, $all, true)) {
                return true;
            }
        }

        return false;
    }

    public function hasRole(string $slug): bool
    {
        return in_array($slug, $this->roleSlugs(), true);
    }

    public function hasAnyRole(array $slugs): bool
    {
        return (bool) array_intersect($slugs, $this->roleSlugs());
    }

    /** Drop the caches after roles or permissions are reassigned. */
    public function forgetAccessCache(): static
    {
        $this->roleCache = null;
        $this->permissionCache = null;

        return $this;
    }

    public function trustedIps()
    {
        return $this->hasMany(UserTrustedIp::class);
    }

    public function bookingsCreated()
    {
        return $this->hasMany(Booking::class, 'created_by');
    }

    public function paymentsReceived()
    {
        return $this->hasMany(Payment::class, 'received_by');
    }

    /** Timestamps formatting */
    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('d M Y h:i A') : '-';
    }

    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('d M Y h:i A') : '-';
    }
}
