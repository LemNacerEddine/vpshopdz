<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AbandonedCart extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'customer_id',
        'session_id',
        'customer_name',
        'customer_phone',
        'customer_email',
        'items',
        'subtotal',
        'recovery_token',
        'recovery_url',
        'status',
        'reminder_sent_at',
        'reminder_count',
        'recovered_at',
        'recovered_order_id',
        'source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
    ];

    protected $casts = [
        'items' => 'array',
        'subtotal' => 'decimal:2',
        'reminder_sent_at' => 'datetime',
        'reminder_count' => 'integer',
        'recovered_at' => 'datetime',
    ];

    const STATUS_ACTIVE = 'active';
    const STATUS_RECOVERED = 'recovered';
    const STATUS_EXPIRED = 'expired';
    const STATUS_IGNORED = 'ignored';

    // ═══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════════════

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function recoveredOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'recovered_order_id');
    }

    // ═══════════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeRecoverable($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
                     ->where('customer_phone', '!=', null)
                     ->where('reminder_count', '<', 3)
                     ->where('created_at', '>=', now()->subDays(7));
    }

    public function scopeForStore($query, string $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeNotRemindedRecently($query, int $hours = 24)
    {
        return $query->where(function ($q) use ($hours) {
            $q->whereNull('reminder_sent_at')
              ->orWhere('reminder_sent_at', '<', now()->subHours($hours));
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    public function markRecovered(string $orderId): void
    {
        $this->update([
            'status' => self::STATUS_RECOVERED,
            'recovered_at' => now(),
            'recovered_order_id' => $orderId,
        ]);
    }

    public function markReminderSent(): void
    {
        $this->update([
            'reminder_sent_at' => now(),
            'reminder_count' => $this->reminder_count + 1,
        ]);
    }

    public function canSendReminder(): bool
    {
        if ($this->status !== self::STATUS_ACTIVE) return false;
        if (!$this->customer_phone) return false;
        if ($this->reminder_count >= 3) return false;
        if ($this->created_at->lt(now()->subDays(7))) return false;
        if ($this->reminder_sent_at && $this->reminder_sent_at->gt(now()->subHours(24))) return false;

        return true;
    }

    public function getItemsCountAttribute(): int
    {
        return count($this->items ?? []);
    }

    public function getTotalQuantityAttribute(): int
    {
        return collect($this->items ?? [])->sum('quantity');
    }
}
