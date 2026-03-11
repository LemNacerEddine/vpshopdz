<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPlan extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'price_monthly',
        'price_yearly',
        'currency',
        'max_products',
        'max_orders_per_month',
        'max_staff',
        'max_categories',
        'max_images_per_product',
        'custom_domain',
        'remove_branding',
        'advanced_analytics',
        'api_access',
        'priority_support',
        'facebook_pixel',
        'whatsapp_integration',
        'abandoned_cart',
        'features',
        'trial_days',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'max_products' => 'integer',
        'max_orders_per_month' => 'integer',
        'max_staff' => 'integer',
        'max_categories' => 'integer',
        'max_images_per_product' => 'integer',
        'custom_domain' => 'boolean',
        'remove_branding' => 'boolean',
        'advanced_analytics' => 'boolean',
        'api_access' => 'boolean',
        'priority_support' => 'boolean',
        'facebook_pixel' => 'boolean',
        'whatsapp_integration' => 'boolean',
        'abandoned_cart' => 'boolean',
        'features' => 'array',
        'trial_days' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    // ═══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════════════

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class, 'plan_id');
    }

    public function stores()
    {
        return $this->hasMany(Store::class, 'plan_id');
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    public function hasFeature(string $feature): bool
    {
        // Check boolean columns first
        $booleanFeatures = [
            'custom_domain', 'remove_branding', 'advanced_analytics',
            'api_access', 'priority_support', 'facebook_pixel',
            'whatsapp_integration', 'abandoned_cart',
        ];

        if (in_array($feature, $booleanFeatures)) {
            return (bool) $this->{$feature};
        }

        // Check features array
        $features = $this->features ?? [];
        return in_array($feature, $features);
    }

    public function getYearlySavingAttribute(): float
    {
        if (!$this->price_yearly || !$this->price_monthly) return 0;
        return ($this->price_monthly * 12) - $this->price_yearly;
    }

    public function isFree(): bool
    {
        return $this->price_monthly == 0;
    }
}
