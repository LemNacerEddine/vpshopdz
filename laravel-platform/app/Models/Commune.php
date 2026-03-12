<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Commune extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'wilaya_id',
        'name_ar',
        'name_fr',
        'postal_code',
    ];

    /**
     * Get the wilaya this commune belongs to
     */
    public function wilaya(): BelongsTo
    {
        return $this->belongsTo(Wilaya::class, 'wilaya_id');
    }

    /**
     * Get shipping rates for this commune
     */
    public function shippingRates(): HasMany
    {
        return $this->hasMany(ShippingRate::class, 'commune_id');
    }

    /**
     * Get localized name
     */
    public function getNameAttribute(): string
    {
        $locale = app()->getLocale();
        $field = "name_{$locale}";
        
        return $this->{$field} ?? $this->name_ar;
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name_ar', 'like', "%{$term}%")
              ->orWhere('name_fr', 'like', "%{$term}%");
        });
    }

    /**
     * Scope to filter by wilaya
     */
    public function scopeInWilaya($query, int $wilayaId)
    {
        return $query->where('wilaya_id', $wilayaId);
    }
}
