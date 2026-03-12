<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'role',
        'store_id',
        'permissions',
        'google_id',
        'reset_token',
        'reset_token_expires_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'reset_token',
        'google_id',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'reset_token_expires_at' => 'datetime',
        ];
    }

    // ═══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════════════

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function ownedStores(): HasMany
    {
        return $this->hasMany(Store::class, 'owner_id');
    }

    // ═══════════════════════════════════════════════════════════════
    // ROLE HELPERS
    // ═══════════════════════════════════════════════════════════════

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isStoreOwner(): bool
    {
        return $this->role === 'store_owner';
    }

    public function isStoreStaff(): bool
    {
        return $this->role === 'store_staff';
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin() || $this->isStoreOwner()) {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    public function canAccessStore(Store $store): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->store_id === $store->id;
    }
}
