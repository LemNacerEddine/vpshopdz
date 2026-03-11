<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FacebookAd extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'product_id',
        'campaign_id',
        'campaign_name',
        'adset_id',
        'creative_id',
        'fb_ad_id',
        'status',
        'error_message',
        'daily_budget_cents',
        'duration_days',
        'target_country',
        'target_age_min',
        'target_age_max',
        'target_interests',
        'ad_text',
        'ad_headline',
        'landing_url',
        'image_hash',
        'impressions',
        'clicks',
        'spend_cents',
        'reach',
        'metrics_updated_at',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'target_interests' => 'array',
        'daily_budget_cents' => 'integer',
        'duration_days' => 'integer',
        'impressions' => 'integer',
        'clicks' => 'integer',
        'spend_cents' => 'integer',
        'reach' => 'integer',
        'metrics_updated_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
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

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForStore($query, string $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    // ═══════════════════════════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════════════════════════

    public function getDailyBudgetAttribute(): float
    {
        return $this->daily_budget_cents / 100;
    }

    public function getSpendAttribute(): float
    {
        return $this->spend_cents / 100;
    }

    public function getCtrAttribute(): float
    {
        if ($this->impressions === 0) return 0;
        return round(($this->clicks / $this->impressions) * 100, 2);
    }

    public function getCpcAttribute(): float
    {
        if ($this->clicks === 0) return 0;
        return round($this->spend / $this->clicks, 2);
    }

    public function getCpmAttribute(): float
    {
        if ($this->impressions === 0) return 0;
        return round(($this->spend / $this->impressions) * 1000, 2);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    public function isRunning(): bool
    {
        return $this->status === 'active' && 
               ($this->ends_at === null || $this->ends_at->isFuture());
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'مسودة',
            'pending' => 'قيد المراجعة',
            'active' => 'نشط',
            'paused' => 'متوقف',
            'completed' => 'مكتمل',
            'error' => 'خطأ',
            default => $this->status,
        };
    }
}
