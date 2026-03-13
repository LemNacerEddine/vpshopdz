<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorePage extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'title',
        'title_ar',
        'slug',
        'content',
        'content_ar',
        'meta_title',
        'meta_description',
        'is_published',
        'sort_order',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'sort_order' => 'integer',
    ];

    // ═══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════════════

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    // ═══════════════════════════════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════════════════════════════

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeForStore($query, string $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // ═══════════════════════════════════════════════════════════════
    // ACCESSORS
    // ═══════════════════════════════════════════════════════════════

    public function getDisplayTitleAttribute(): string
    {
        $locale = app()->getLocale();
        return match($locale) {
            'ar' => $this->title_ar ?? $this->title,
            default => $this->title,
        };
    }

    public function getDisplayContentAttribute(): string
    {
        $locale = app()->getLocale();
        return match($locale) {
            'ar' => $this->content_ar ?? $this->content,
            default => $this->content,
        };
    }
}
