<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'name_ar',
        'name_fr',
        'slug',
        'description',
        'description_ar',
        'description_fr',
        'price',
        'compare_at_price',
        'cost_price',
        'discount_percent',
        'discount_starts_at',
        'discount_ends_at',
        'sku',
        'barcode',
        'stock_quantity',
        'track_inventory',
        'allow_backorder',
        'low_stock_threshold',
        'shipping_type',
        'fixed_shipping_price',
        'weight',
        'length',
        'width',
        'height',
        'is_fragile',
        'unit',
        'status',
        'has_variants',
        'is_featured',
        'meta_title',
        'meta_description',
        'rating',
        'reviews_count',
        'sold_count',
        'views_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'fixed_shipping_price' => 'decimal:2',
        'discount_percent' => 'integer',
        'discount_starts_at' => 'datetime',
        'discount_ends_at' => 'datetime',
        'stock_quantity' => 'integer',
        'track_inventory' => 'boolean',
        'allow_backorder' => 'boolean',
        'is_fragile' => 'boolean',
        'has_variants' => 'boolean',
        'is_featured' => 'boolean',
        'weight' => 'decimal:2',
        'rating' => 'decimal:1',
        'reviews_count' => 'integer',
        'sold_count' => 'integer',
        'views_count' => 'integer',
    ];

    // ═══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════════════

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->where('is_active', true);
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class)->orderBy('position');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // ═══════════════════════════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════════════════════════

    public function getPrimaryImageAttribute(): ?string
    {
        $primaryImage = $this->images->firstWhere('is_primary', true);
        return $primaryImage?->url ?? $this->images->first()?->url;
    }

    public function getImagesUrlsAttribute(): array
    {
        return $this->images->pluck('url')->toArray();
    }

    public function getFinalPriceAttribute(): float
    {
        if (!$this->hasActiveDiscount()) {
            return $this->price;
        }

        return round($this->price * (1 - $this->discount_percent / 100), 2);
    }

    public function getDisplayNameAttribute(): string
    {
        $locale = app()->getLocale();
        
        return match($locale) {
            'ar' => $this->name_ar ?? $this->name,
            'fr' => $this->name_fr ?? $this->name,
            default => $this->name,
        };
    }

    // ═══════════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_inventory', false)
              ->orWhere('stock_quantity', '>', 0)
              ->orWhere('allow_backorder', true);
        });
    }

    public function scopeOnSale($query)
    {
        return $query->where('discount_percent', '>', 0)
            ->where(function ($q) {
                $q->whereNull('discount_starts_at')
                  ->orWhere('discount_starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('discount_ends_at')
                  ->orWhere('discount_ends_at', '>=', now());
            });
    }

    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    public function hasActiveDiscount(): bool
    {
        if ($this->discount_percent <= 0) {
            return false;
        }

        $now = now();

        if ($this->discount_starts_at && $now->lt($this->discount_starts_at)) {
            return false;
        }

        if ($this->discount_ends_at && $now->gt($this->discount_ends_at)) {
            return false;
        }

        return true;
    }

    public function isInStock(): bool
    {
        if (!$this->track_inventory) {
            return true;
        }

        return $this->stock_quantity > 0 || $this->allow_backorder;
    }

    public function decrementStock(int $quantity): void
    {
        if ($this->track_inventory) {
            $this->decrement('stock_quantity', $quantity);
        }
        $this->increment('sold_count', $quantity);
    }

    public function updateRating(): void
    {
        $reviews = $this->reviews()->where('is_approved', true);
        
        $this->update([
            'rating' => round($reviews->avg('rating'), 1) ?? 0,
            'reviews_count' => $reviews->count(),
        ]);
    }
}
