<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreIntegration extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'type',
        'name',
        'credentials',
        'settings',
        'is_active',
        'last_synced_at',
        'last_error',
    ];

    protected $casts = [
        'credentials' => 'encrypted:array',
        'settings' => 'array',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    protected $hidden = [
        'credentials',
    ];

    const TYPES = [
        'whatsapp_green_api' => 'واتساب (Green API)',
        'whatsapp_business' => 'واتساب بيزنس',
        'telegram_bot' => 'بوت تيليجرام',
        'facebook_marketing' => 'فيسبوك ماركتنج',
        'google_merchant' => 'Google Merchant',
        'yalidine_api' => 'يالدين API',
        'zr_express_api' => 'ZR Express API',
        'echrily_api' => 'اشريلي API',
        'maystro_api' => 'مايسترو API',
        'custom_webhook' => 'Webhook مخصص',
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

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->type] ?? $this->type;
    }

    public function getCredential(string $key, $default = null)
    {
        return data_get($this->credentials, $key, $default);
    }

    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function markSynced(): void
    {
        $this->update([
            'last_synced_at' => now(),
            'last_error' => null,
        ]);
    }

    public function markError(string $error): void
    {
        $this->update([
            'last_error' => $error,
        ]);
    }

    public function isWhatsApp(): bool
    {
        return in_array($this->type, ['whatsapp_green_api', 'whatsapp_business']);
    }

    public function isShippingApi(): bool
    {
        return in_array($this->type, ['yalidine_api', 'zr_express_api', 'echrily_api', 'maystro_api']);
    }
}
