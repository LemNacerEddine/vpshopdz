<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'code',
        'type',
        'value',
        'min_order_amount',
        'max_discount',
        'max_uses',
        'max_uses_per_customer',
        'used_count',
        'starts_at',
        'expires_at',
        'product_ids',
        'category_ids',
        'is_active',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'product_ids' => 'array',
        'category_ids' => 'array',
        'is_active' => 'boolean',
    ];

    // ═══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════════════

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    public function isValid(): bool
    {
        if (!$this->is_active) return false;

        if ($this->starts_at && $this->starts_at->isFuture()) return false;

        if ($this->expires_at && $this->expires_at->isPast()) return false;

        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;

        return true;
    }

    public function getInvalidReason(): string
    {
        if (!$this->is_active) return 'كود الخصم غير مفعل';
        if ($this->starts_at && $this->starts_at->isFuture()) return 'كود الخصم لم يبدأ بعد';
        if ($this->expires_at && $this->expires_at->isPast()) return 'كود الخصم منتهي الصلاحية';
        if ($this->max_uses && $this->used_count >= $this->max_uses) return 'تم استخدام كود الخصم بالكامل';
        return 'كود الخصم غير صالح';
    }

    public function calculateDiscount(float $subtotal): float
    {
        $discount = 0;

        if ($this->type === 'percentage') {
            $discount = $subtotal * ($this->value / 100);
        } else {
            $discount = $this->value;
        }

        // Apply max discount cap
        if ($this->max_discount && $discount > $this->max_discount) {
            $discount = $this->max_discount;
        }

        // Don't exceed subtotal
        return min($discount, $subtotal);
    }
}
