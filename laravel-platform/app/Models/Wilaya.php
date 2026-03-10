<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wilaya extends Model
{
    public $incrementing = false;
    public $timestamps = false;
    
    protected $primaryKey = 'id';
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'name_ar',
        'name_fr',
        'name_en',
    ];

    /**
     * Get all communes in this wilaya
     */
    public function communes(): HasMany
    {
        return $this->hasMany(Commune::class, 'wilaya_id');
    }

    /**
     * Get shipping rates for this wilaya
     */
    public function shippingRates(): HasMany
    {
        return $this->hasMany(ShippingRate::class, 'wilaya_id');
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
}
