<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'resource',
        'action',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'permission_user')
            ->withPivot(['granted_by', 'granted_at', 'expires_at', 'reason'])
            ->withTimestamps();
    }

    public function scopeForResource($query, string $resource)
    {
        return $query->where('resource', $resource);
    }

    public function scopeForAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    public static function createFor(string $resource, string $action): self
    {
        return static::create([
            'name' => ucfirst($action).' '.ucfirst($resource),
            'slug' => "{$resource}.{$action}",
            'resource' => $resource,
            'action' => $action,
        ]);
    }
}
