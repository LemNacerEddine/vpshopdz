<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Theme extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'name_ar',
        'slug',
        'description',
        'description_ar',
        'thumbnail',
        'preview_url',
        'category',
        'default_colors',
        'default_fonts',
        'default_layout',
        'sections',
        'settings_schema',
        'is_free',
        'price',
        'is_active',
        'is_default',
        'installs_count',
        'sort_order',
        'version',
        'author',
    ];

    protected $casts = [
        'default_colors' => 'array',
        'default_fonts' => 'array',
        'default_layout' => 'array',
        'sections' => 'array',
        'settings_schema' => 'array',
        'is_free' => 'boolean',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'installs_count' => 'integer',
        'sort_order' => 'integer',
    ];

    // ═══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════════════

    public function storeThemes(): HasMany
    {
        return $this->hasMany(StoreTheme::class);
    }

    // ═══════════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFree($query)
    {
        return $query->where('is_free', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
