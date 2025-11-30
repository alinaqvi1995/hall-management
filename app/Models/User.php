<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\LogsActivity;

class User extends Authenticatable
{
    use LogsActivity, HasFactory, Notifiable;

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

    public function getStatusFormattedAttribute()
    {
        return $this->status == 1 ? 'Active' : 'Inactive';
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
        return $this->roles()->where('slug', 'super_admin')->exists();
    }

    public function isHallAdmin(): bool
    {
        return $this->roles()->where('slug', 'hall_admin')->exists();
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

    public function hasPermission(string $slug): bool
    {
        return $this->allPermissions()->pluck('slug')->contains($slug);
    }

    public function hasAllPermissions(array $slugs): bool
    {
        $all = $this->allPermissions()->pluck('slug')->toArray();

        return collect($slugs)->every(fn($slug) => in_array($slug, $all));
    }

    public function hasAnyPermission(array $slugs): bool
    {
        $all = $this->allPermissions()->pluck('slug')->toArray();
        return collect($slugs)->contains(fn($slug) => in_array($slug, $all));
    }

    public function hasRole(string $slug): bool
    {
        return $this->roles()->where('slug', $slug)->exists();
    }

    public function hasAnyRole(array $slugs): bool
    {
        return $this->roles()->whereIn('slug', $slugs)->exists();
    }

    public function trustedIps()
    {
        return $this->hasMany(UserTrustedIp::class);
    }

    /** Timestamps formatting */

    public function getCreatedAtFormattedAttribute()
    {
        return $this->created_at ? $this->created_at->format('Md,  Y h:ia') : '-';
    }

    public function getUpdatedAtFormattedAttribute()
    {
        return $this->updated_at ? $this->updated_at->format('Md, Y h:ia') : '-';
    }
}
