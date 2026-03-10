<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'store_id',
        'name',
        'name_ar',
        'name_fr',
        'slug',
        'description',
        'description_ar',
        'image',
        'icon',
        'parent_id',
        'sort_order',
        'is_active',
        'products_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'products_count' => 'integer',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeForStore($query, $storeId)
    {
        return $query->where('store_id', $storeId);
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
}
