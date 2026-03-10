<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreShippingSetting extends Model
{
    use HasUuids;

    protected $fillable = [
        'store_id',
        'company_id',
        'api_key',
        'api_secret',
        'is_active',
        'custom_rates',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'custom_rates' => 'array',
    ];

    protected $hidden = [
        'api_key',
        'api_secret',
    ];

    /**
     * Get the store
     */
    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Get the shipping company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class, 'company_id');
    }

    /**
     * Scope for active settings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
