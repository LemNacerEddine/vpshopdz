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

    public function incrementOrdersCount(): void
    {
        $this->increment('orders_count');
        
        if ($this->subscription) {
            $this->subscription->increment('orders_this_month');
        }
    }
}
