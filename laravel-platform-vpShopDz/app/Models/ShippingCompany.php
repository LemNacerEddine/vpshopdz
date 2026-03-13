<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingCompany extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'name_ar',
        'code',
        'logo',
        'phone',
        'email',
        'website',
        'api_url',
        'api_key',
        'api_secret',
        'tracking_url_template',
        'volumetric_divisor',
        'included_weight',
        'price_per_extra_kg',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'volumetric_divisor' => 'integer',
        'included_weight' => 'decimal:2',
        'price_per_extra_kg' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
    ];

    /**
     * Get shipping rates for this company
     */
    public function rates(): HasMany
    {
        return $this->hasMany(ShippingRate::class, 'company_id');
    }

    /**
     * Get store settings for this company
     */
    public function storeSettings(): HasMany
    {
        return $this->hasMany(StoreShippingSetting::class, 'company_id');
    }

    /**
     * Get localized name
     */
    public function getLocalizedNameAttribute(): string
    {
        $locale = app()->getLocale();
        
        if ($locale === 'ar' && $this->name_ar) {
            return $this->name_ar;
        }
        
        return $this->name;
    }

    /**
     * Generate tracking URL
     */
    public function getTrackingUrl(string $trackingNumber): ?string
    {
        if (!$this->tracking_url_template) {
            return null;
        }

        return str_replace('{tracking}', $trackingNumber, $this->tracking_url_template);
    }

    /**
     * Scope for active companies
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope ordered by sort_order
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    /**
     * Scope for companies with API integration
     */
    public function scopeWithApi($query)
    {
        return $query->whereNotNull('api_url');
    }
}
