<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Store extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'owner_id',
        'name',
        'name_ar',
        'slug',
        'description',
        'description_ar',
        'logo',
        'favicon',
        'cover_image',
        'email',
        'phone',
        'whatsapp',
        'address',
        'currency',
        'language',
        'timezone',
        'theme_id',
        'subdomain',
        'custom_domain',
        'ssl_enabled',
        'facebook_pixel_id',
        'tiktok_pixel_id',
        'google_analytics_id',
        'snapchat_pixel_id',
        'facebook_url',
        'instagram_url',
        'tiktok_url',
        'status',
        'trial_ends_at',
        'suspended_at',
        'suspension_reason',
        'products_count',
        'orders_count',
        'total_revenue',
        'settings',
        'shipping_settings',
        'payment_settings',
        'notification_settings',
    ];

    protected $casts = [
        'ssl_enabled' => 'boolean',
        'trial_ends_at' => 'datetime',
        'suspended_at' => 'datetime',
        'products_count' => 'integer',
        'orders_count' => 'integer',
        'total_revenue' => 'decimal:2',
        'settings' => 'array',
        'shipping_settings' => 'array',
        'payment_settings' => 'array',
        'notification_settings' => 'array',
    ];

    // ═══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════════════

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(StoreSubscription::class)->latest();
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(StoreSubscription::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function shippingRules(): HasMany
    {
        return $this->hasMany(ShippingRule::class);
    }

    public function analytics(): HasMany
    {
        return $this->hasMany(StoreAnalytics::class);
    }

    public function shippingSettings(): HasMany
    {
        return $this->hasMany(StoreShippingSetting::class);
    }

    // ─── New Relationships ───────────────────────────────────────

    public function activeTheme(): HasOne
    {
        return $this->hasOne(StoreTheme::class)->where('is_active', true);
    }

    public function themes(): HasMany
    {
        return $this->hasMany(StoreTheme::class);
    }

    public function pixels(): HasMany
    {
        return $this->hasMany(StorePixel::class);
    }

    public function activePixels(): HasMany
    {
        return $this->hasMany(StorePixel::class)->where('is_active', true);
    }

    public function facebookAds(): HasMany
    {
        return $this->hasMany(FacebookAd::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(StorePage::class);
    }

    public function notificationTemplates(): HasMany
    {
        return $this->hasMany(NotificationTemplate::class);
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(StoreIntegration::class);
    }

    public function domains(): HasMany
    {
        return $this->hasMany(StoreDomain::class);
    }

    public function primaryDomain(): HasOne
    {
        return $this->hasOne(StoreDomain::class)->where('is_primary', true);
    }

    public function abandonedCarts(): HasMany
    {
        return $this->hasMany(AbandonedCart::class);
    }

    public function staffInvitations(): HasMany
    {
        return $this->hasMany(StaffInvitation::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // ═══════════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTrial($query)
    {
        return $query->where('status', 'trial');
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', 'suspended');
    }

    public function scopeByDomain($query, string $domain)
    {
        return $query->where('subdomain', $domain)
                     ->orWhere('custom_domain', $domain);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isTrial(): bool
    {
        return $this->status === 'trial';
    }

    public function isTrialExpired(): bool
    {
        return $this->isTrial() && $this->trial_ends_at && $this->trial_ends_at->isPast();
    }

    public function isAccessible(): bool
    {
        if ($this->isActive()) return true;
        if ($this->isTrial() && !$this->isTrialExpired()) return true;
        return false;
    }

    public function getUrl(): string
    {
        if ($this->custom_domain) {
            $protocol = $this->ssl_enabled ? 'https' : 'http';
            return "{$protocol}://{$this->custom_domain}";
        }
        
        return "https://{$this->subdomain}." . config('app.platform_domain');
    }

    public function hasActiveSubscription(): bool
    {
        return $this->subscription && $this->subscription->status === 'active';
    }

    public function canCreateProduct(): bool
    {
        if (!$this->subscription) return true; // Trial
        
        $plan = $this->subscription->plan;
        if (!$plan->max_products) return true;
        
        return $this->products_count < $plan->max_products;
    }

    public function canCreateOrder(): bool
    {
        if (!$this->subscription) return true; // Trial
        
        $plan = $this->subscription->plan;
        if (!$plan->max_orders_per_month) return true;
        
        return $this->subscription->orders_this_month < $plan->max_orders_per_month;
    }

    public function canUseFeature(string $feature): bool
    {
        if (!$this->subscription || !$this->subscription->plan) {
            return false;
        }

        return (bool) $this->subscription->plan->{$feature};
    }

    public function incrementOrdersCount(): void
    {
        $this->increment('orders_count');
        
        if ($this->subscription) {
            $this->subscription->increment('orders_this_month');
        }
    }

    public function incrementProductsCount(): void
    {
        $this->increment('products_count');
    }

    public function decrementProductsCount(): void
    {
        $this->decrement('products_count');
    }

    /**
     * Get the integration for a specific type
     */
    public function getIntegration(string $type): ?StoreIntegration
    {
        return $this->integrations()->where('type', $type)->where('is_active', true)->first();
    }

    /**
     * Get active pixel scripts for storefront
     */
    public function getPixelScripts(): string
    {
        return $this->activePixels
            ->map(fn($pixel) => $pixel->generateScript())
            ->filter()
            ->implode("\n");
    }

    /**
     * Get the store's active theme with customizations
     */
    public function getThemeConfig(): array
    {
        $storeTheme = $this->activeTheme;

        if (!$storeTheme) {
            return [
                'colors' => ['primary' => '#10b981', 'secondary' => '#3b82f6'],
                'fonts' => ['heading' => 'Cairo', 'body' => 'Cairo'],
                'layout' => ['style' => 'modern'],
            ];
        }

        return [
            'theme_id' => $storeTheme->theme_id,
            'theme_name' => $storeTheme->theme->name ?? 'Default',
            'colors' => $storeTheme->getMergedColors(),
            'fonts' => $storeTheme->getMergedFonts(),
            'layout' => $storeTheme->getMergedLayout(),
            'sections' => $storeTheme->getMergedSections(),
            'header' => $storeTheme->header_settings ?? [],
            'footer' => $storeTheme->footer_settings ?? [],
            'homepage' => $storeTheme->homepage_settings ?? [],
            'custom_css' => $storeTheme->custom_css ?? [],
        ];
    }
}
