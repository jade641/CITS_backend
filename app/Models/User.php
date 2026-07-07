<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'phone',
        'department',
        'job_title',
        'status',
        'role',
        'last_login_at',
        'last_login_ip',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'primary_role',
        'permission_slugs',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)
            ->withPivot(['assigned_by'])
            ->withTimestamps();
    }

    public function reportedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'reporter_id');
    }

    public function assignedIncidents(): HasMany
    {
        return $this->hasMany(Incident::class, 'current_assignee_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class, 'generated_by');
    }

    public function hasRole(string $role): bool
    {
        $roleSlug = str($role)->slug()->toString();

        return $this->roles->contains(static fn (Role $userRole): bool => $userRole->slug === $roleSlug);
    }

    /**
     * @param  array<int, string>  $roles
     */
    public function hasAnyRole(array $roles): bool
    {
        $slugs = array_map(static fn (string $role): string => str($role)->slug()->toString(), $roles);

        return $this->roles->contains(static fn (Role $userRole): bool => in_array($userRole->slug, $slugs, true));
    }

    public function hasPermission(string $permission): bool
    {
        $permissionSlug = str($permission)->lower()->trim()->toString();

        return $this->roles
            ->loadMissing('permissions')
            ->flatMap(static fn (Role $role) => $role->permissions)
            ->contains(static fn (Permission $rolePermission): bool => $rolePermission->slug === $permissionSlug);
    }

    public function getPrimaryRoleAttribute(): ?string
    {
        return $this->roles->sortBy('id')->first()?->slug;
    }

    public function isSocAnalyst(): bool
    {
        return $this->hasAnyRole(['administrator', 'security-analyst']);
    }

    /**
     * @return array<int, string>
     */
    public function getPermissionSlugsAttribute(): array
    {
        return $this->roles
            ->loadMissing('permissions')
            ->flatMap(static fn (Role $role) => $role->permissions)
            ->pluck('slug')
            ->unique()
            ->values()
            ->all();
    }

    public function isSocSupervisor(): bool
    {
        return $this->hasAnyRole(['administrator']);
    }
}
