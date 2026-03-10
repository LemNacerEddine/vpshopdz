<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingRate extends Model
{
    use HasUuids;

    protected $fillable = [
        'company_id',
        'wilaya_id',
        'commune_id',
        'delivery_type',
        'price',
        'min_days',
        'max_days',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'min_days' => 'integer',
        'max_days' => 'integer',
        'is_active' => 'boolean',
    ];

    /**
     * Get the shipping company
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(ShippingCompany::class, 'company_id');
    }

    /**
     * Get the wilaya
     */
    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class, 'wilaya_id');
    }

    /**
     * Get the commune (optional)
     */
    public function commune(): BelongsTo
    {
        return $this->belongsTo(Commune::class, 'commune_id');
    }

    /**
     * Get delivery days as string
     */
    public function getDeliveryDaysAttribute(): string
    {
        if ($this->min_days === $this->max_days) {
            return "{$this->min_days} " . __('days');
        }
        
        return "{$this->min_days}-{$this->max_days} " . __('days');
    }

    /**
     * Scope for active rates
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for home delivery
     */
    public function scopeHomeDelivery($query)
    {
        return $query->where('delivery_type', 'home');
    }

    /**
     * Scope for desk/office delivery
     */
    public function scopeDeskDelivery($query)
    {
        return $query->where('delivery_type', 'desk');
    }

    /**
     * Scope for specific wilaya
     */
    public function scopeForWilaya($query, int $wilayaId)
    {
        return $query->where('wilaya_id', $wilayaId);
    }

    /**
     * Scope for specific commune
     */
    public function scopeForCommune($query, int $communeId)
    {
        return $query->where('commune_id', $communeId);
    }
}
