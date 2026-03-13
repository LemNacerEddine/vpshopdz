<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationTemplate extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'channel',
        'event',
        'name',
        'template_ar',
        'template_fr',
        'template_en',
        'is_active',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    const CHANNELS = [
        'whatsapp' => 'واتساب',
        'telegram' => 'تيليجرام',
        'sms' => 'رسالة نصية',
        'email' => 'بريد إلكتروني',
    ];

    const EVENTS = [
        'order_created' => 'طلب جديد',
        'order_confirmed' => 'تأكيد الطلب',
        'order_shipped' => 'شحن الطلب',
        'order_delivered' => 'تسليم الطلب',
        'order_cancelled' => 'إلغاء الطلب',
        'abandoned_cart' => 'سلة متروكة',
        'welcome_customer' => 'ترحيب بالزبون',
        'custom' => 'مخصص',
    ];

    // Available template variables
    const VARIABLES = [
        '{{customer_name}}' => 'اسم الزبون',
        '{{order_number}}' => 'رقم الطلب',
        '{{order_total}}' => 'مجموع الطلب',
        '{{order_status}}' => 'حالة الطلب',
        '{{tracking_number}}' => 'رقم التتبع',
        '{{store_name}}' => 'اسم المتجر',
        '{{store_phone}}' => 'هاتف المتجر',
        '{{product_name}}' => 'اسم المنتج',
        '{{cart_url}}' => 'رابط السلة',
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

    public function scopeForChannel($query, string $channel)
    {
        return $query->where('channel', $channel);
    }

    public function scopeForEvent($query, string $event)
    {
        return $query->where('event', $event);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Render template with variables
     */
    public function render(array $data, string $locale = 'ar'): string
    {
        $template = match($locale) {
            'ar' => $this->template_ar,
            'fr' => $this->template_fr,
            'en' => $this->template_en,
            default => $this->template_ar,
        };

        if (!$template) {
            return '';
        }

        foreach ($data as $key => $value) {
            $template = str_replace("{{{$key}}}", $value, $template);
            $template = str_replace("{{" . $key . "}}", $value, $template);
        }

        return $template;
    }

    public function getChannelLabelAttribute(): string
    {
        return self::CHANNELS[$this->channel] ?? $this->channel;
    }

    public function getEventLabelAttribute(): string
    {
        return self::EVENTS[$this->event] ?? $this->event;
    }
}
