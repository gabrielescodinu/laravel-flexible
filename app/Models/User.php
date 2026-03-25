<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @var Collection<int, string>|null
     */
    private ?Collection $cachedPermissionNames = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)->withTimestamps();
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class)->orderByDesc('id');
    }

    public function hasPermission(string $permissionName): bool
    {
        $permissionName = trim($permissionName);
        if ($permissionName === '') {
            return false;
        }

        $permissionNames = $this->resolvedPermissionNames();
        if ($permissionNames->contains('*')) {
            return true;
        }

        return $permissionNames->contains($permissionName);
    }

    /**
     * @param  list<string>  $permissionNames
     */
    public function hasAnyPermission(array $permissionNames): bool
    {
        $permissionNames = array_values(array_filter(array_map('trim', $permissionNames)));
        if ($permissionNames === []) {
            return false;
        }

        $resolvedPermissionNames = $this->resolvedPermissionNames();
        if ($resolvedPermissionNames->contains('*')) {
            return true;
        }

        foreach ($permissionNames as $permissionName) {
            if ($resolvedPermissionNames->contains($permissionName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return Collection<int, string>
     */
    private function resolvedPermissionNames(): Collection
    {
        if ($this->cachedPermissionNames !== null) {
            return $this->cachedPermissionNames;
        }

        if (! $this->permissionsTablesExist()) {
            $this->cachedPermissionNames = collect(['*']);

            return $this->cachedPermissionNames;
        }

        if (! $this->assignmentsConfigured()) {
            $this->cachedPermissionNames = collect(['*']);

            return $this->cachedPermissionNames;
        }

        $hasDirectAssignments = $this->roles()->exists() || $this->permissions()->exists();
        if (! $hasDirectAssignments) {
            $this->cachedPermissionNames = ((int) $this->getKey() === 1)
                ? collect(['*'])
                : collect();

            return $this->cachedPermissionNames;
        }

        $isAdministrator = $this->roles()
            ->where('slug', 'administrator')
            ->exists();

        if ($isAdministrator) {
            $this->cachedPermissionNames = collect(['*']);

            return $this->cachedPermissionNames;
        }

        $directPermissions = $this->permissions()
            ->pluck('permissions.name');

        $rolePermissions = Permission::query()
            ->whereHas('roles.users', fn ($query) => $query->whereKey($this->id))
            ->pluck('permissions.name');

        $this->cachedPermissionNames = $directPermissions
            ->merge($rolePermissions)
            ->unique()
            ->values();

        return $this->cachedPermissionNames;
    }

    private function permissionsTablesExist(): bool
    {
        static $cache = null;

        if ($cache !== null) {
            return $cache;
        }

        $cache = Schema::hasTable('roles')
            && Schema::hasTable('permissions')
            && Schema::hasTable('role_user')
            && Schema::hasTable('permission_role')
            && Schema::hasTable('permission_user');

        return $cache;
    }

    private function assignmentsConfigured(): bool
    {
        return DB::table('role_user')->exists()
            || DB::table('permission_user')->exists();
    }
}
