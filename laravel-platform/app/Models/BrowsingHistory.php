<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BrowsingHistory extends Model
{
    use HasFactory, HasUuids;

    public $timestamps = false;

    protected $table = 'browsing_history';

    protected $fillable = [
        'store_id',
        'customer_id',
        'session_id',
        'product_id',
        'viewed_at',
    ];

    protected $casts = [
        'viewed_at' => 'datetime',
    ];

    // ═══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════════════

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // ═══════════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════════

    public function scopeForStore($query, string $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('viewed_at', '>=', now()->subDays($days));
    }

    // ═══════════════════════════════════════════════════════════════
    // STATIC HELPERS
    // ═══════════════════════════════════════════════════════════════

    public static function recordView(string $storeId, string $productId, ?string $sessionId = null, ?string $customerId = null): void
    {
        static::create([
            'store_id' => $storeId,
            'product_id' => $productId,
            'session_id' => $sessionId,
            'customer_id' => $customerId,
            'viewed_at' => now(),
        ]);
    }
}
