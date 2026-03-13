<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Authenticatable
{
    use HasFactory, HasUuids, HasApiTokens, Notifiable;

    protected $fillable = [
        'store_id', 'name', 'email', 'phone', 'phone2', 'password',
        'wilaya', 'commune', 'address', 'orders_count', 'total_spent',
        'last_order_at', 'notes', 'tags',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'orders_count' => 'integer',
        'total_spent' => 'decimal:2',
        'last_order_at' => 'datetime',
        'tags' => 'array',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public static function findOrCreateByPhone(string $storeId, string $phone, array $data = []): self
    {
        return static::firstOrCreate(
            ['store_id' => $storeId, 'phone' => $phone],
            array_merge(['name' => $data['name'] ?? 'زبون'], $data)
        );
    }

    public function updateFromOrder(Order $order): void
    {
        $this->increment('orders_count');
        $this->increment('total_spent', $order->total);
        $this->update(['last_order_at' => now()]);
    }
}
