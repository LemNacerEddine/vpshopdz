<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSubscription extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'plan_id',
        'status',
        'billing_cycle',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'orders_this_month',
        'products_count',
        'amount_paid',
        'payment_method',
        'payment_reference',
        'last_payment_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'last_payment_at' => 'datetime',
        'orders_this_month' => 'integer',
        'products_count' => 'integer',
        'amount_paid' => 'decimal:2',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->ends_at->isFuture();
    }

    public function isExpired(): bool
    {
        return $this->ends_at->isPast();
    }

    public function daysRemaining(): int
    {
        return max(0, now()->diffInDays($this->ends_at, false));
    }

    public function canCreateOrder(): bool
    {
        if (!$this->plan->max_orders_per_month) {
            return true;
        }
        return $this->orders_this_month < $this->plan->max_orders_per_month;
    }

    public function resetMonthlyCounters(): void
    {
        $this->update(['orders_this_month' => 0]);
    }
}
