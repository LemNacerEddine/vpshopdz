<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreDomain extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'domain',
        'type',
        'is_primary',
        'ssl_status',
        'ssl_expires_at',
        'dns_status',
        'dns_verified_at',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'ssl_expires_at' => 'datetime',
        'dns_verified_at' => 'datetime',
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

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeVerified($query)
    {
        return $query->where('dns_status', 'verified');
    }

    public function scopeCustom($query)
    {
        return $query->where('type', 'custom');
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    public function getFullUrlAttribute(): string
    {
        $protocol = $this->ssl_status === 'active' ? 'https' : 'http';
        return "{$protocol}://{$this->domain}";
    }

    public function isDnsVerified(): bool
    {
        return $this->dns_status === 'verified';
    }

    public function isSslActive(): bool
    {
        return $this->ssl_status === 'active' && 
               ($this->ssl_expires_at === null || $this->ssl_expires_at->isFuture());
    }

    public function markDnsVerified(): void
    {
        $this->update([
            'dns_status' => 'verified',
            'dns_verified_at' => now(),
        ]);
    }

    public function markSslActive(\DateTime $expiresAt = null): void
    {
        $this->update([
            'ssl_status' => 'active',
            'ssl_expires_at' => $expiresAt ?? now()->addYear(),
        ]);
    }
}
