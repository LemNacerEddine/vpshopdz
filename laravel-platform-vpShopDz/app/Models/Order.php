<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'store_id',
        'customer_id',
        'order_number',
        'customer_name',
        'customer_phone',
        'customer_phone2',
        'customer_email',
        'wilaya',
        'commune',
        'shipping_address',
        'subtotal',
        'shipping_cost',
        'discount_amount',
        'discount_percent',
        'total',
        'status',
        'payment_status',
        'payment_method',
        'shipping_company_id',
        'tracking_number',
        'shipped_at',
        'delivered_at',
        'customer_notes',
        'internal_notes',
        'source',
        'utm_source',
        'utm_medium',
        'utm_campaign',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    // الحالات
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_RETURNED = 'returned';

    const STATUSES = [
        self::STATUS_PENDING => 'في الانتظار',
        self::STATUS_CONFIRMED => 'مؤكد',
        self::STATUS_PROCESSING => 'قيد التجهيز',
        self::STATUS_SHIPPED => 'تم الشحن',
        self::STATUS_DELIVERED => 'تم التوصيل',
        self::STATUS_CANCELLED => 'ملغي',
        self::STATUS_RETURNED => 'مسترجع',
    ];

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

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderByDesc('created_at');
    }

    public function shippingCompany(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class);
    }

    // ═══════════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════════

    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    public function scopeProcessing($query)
    {
        return $query->whereIn('status', [
            self::STATUS_CONFIRMED,
            self::STATUS_PROCESSING,
        ]);
    }

    public function scopeShipped($query)
    {
        return $query->where('status', self::STATUS_SHIPPED);
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', self::STATUS_DELIVERED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_CONFIRMED => 'blue',
            self::STATUS_PROCESSING => 'indigo',
            self::STATUS_SHIPPED => 'purple',
            self::STATUS_DELIVERED => 'green',
            self::STATUS_CANCELLED => 'red',
            self::STATUS_RETURNED => 'orange',
            default => 'gray',
        };
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isDelivered(): bool
    {
        return $this->status === self::STATUS_DELIVERED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
        ]);
    }

    public function updateStatus(string $newStatus, ?string $notes = null, ?User $changedBy = null): void
    {
        $oldStatus = $this->status;
        
        $this->update(['status' => $newStatus]);

        // Record status history
        $this->statusHistory()->create([
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'notes' => $notes,
            'changed_by' => $changedBy?->id,
        ]);

        // Update timestamps
        if ($newStatus === self::STATUS_SHIPPED) {
            $this->update(['shipped_at' => now()]);
        } elseif ($newStatus === self::STATUS_DELIVERED) {
            $this->update(['delivered_at' => now()]);
        }
    }

    public static function generateOrderNumber(string $storeId): string
    {
        $prefix = 'ORD';
        $date = now()->format('ymd');
        $count = static::where('store_id', $storeId)
                       ->whereDate('created_at', today())
                       ->count() + 1;
        
        return sprintf('%s-%s-%04d', $prefix, $date, $count);
    }
}
