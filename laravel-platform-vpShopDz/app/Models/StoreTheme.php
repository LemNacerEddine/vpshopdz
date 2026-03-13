<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreTheme extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'theme_id',
        'is_active',
        'custom_colors',
        'custom_fonts',
        'custom_layout',
        'custom_sections',
        'custom_css',
        'header_settings',
        'footer_settings',
        'homepage_settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'custom_colors' => 'array',
        'custom_fonts' => 'array',
        'custom_layout' => 'array',
        'custom_sections' => 'array',
        'custom_css' => 'array',
        'header_settings' => 'array',
        'footer_settings' => 'array',
        'homepage_settings' => 'array',
    ];

    // ═══════════════════════════════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════════════════════════════

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get merged settings (theme defaults + store customizations)
     */
    public function getMergedColors(): array
    {
        return array_merge(
            $this->theme->default_colors ?? [],
            $this->custom_colors ?? []
        );
    }

    public function getMergedFonts(): array
    {
        return array_merge(
            $this->theme->default_fonts ?? [],
            $this->custom_fonts ?? []
        );
    }

    public function getMergedLayout(): array
    {
        return array_merge(
            $this->theme->default_layout ?? [],
            $this->custom_layout ?? []
        );
    }

    public function getMergedSections(): array
    {
        $defaultSections = $this->theme->sections ?? [];
        $customSections = $this->custom_sections ?? [];

        if (empty($customSections)) {
            return $defaultSections;
        }

        return $customSections;
    }
}
