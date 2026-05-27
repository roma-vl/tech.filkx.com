<?php

namespace App\Models;

use App\Api\V1\Enum\RoleScopeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'scope', 'is_system'];

    protected $casts = [
        'is_system' => 'boolean',
        'scope' => RoleScopeEnum::class,
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_role')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'role_user')
            ->withPivot(['granted_by', 'granted_at', 'expires_at'])
            ->withTimestamps();
    }

    public function scopeGlobal($query)
    {
        return $query->where('scope', 'global');
    }

    public function scopeContextual($query)
    {
        return $query->where('scope', 'contextual');
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function isGlobal(): bool
    {
        return $this->scope === 'global';
    }

    public function isContextual(): bool
    {
        return $this->scope === 'contextual';
    }

    public function hasPermission(string $permissionSlug): bool
    {
        return $this->permissions()->where('slug', $permissionSlug)->exists();
    }

    public function givePermissions(array $permissionSlugs): void
    {
        $permissions = Permission::whereIn('slug', $permissionSlugs)->pluck('id');
        $this->permissions()->syncWithoutDetaching($permissions);
    }

    public function revokePermissions(array $permissionSlugs): void
    {
        $permissions = Permission::whereIn('slug', $permissionSlugs)->pluck('id');
        $this->permissions()->detach($permissions);
    }
}
