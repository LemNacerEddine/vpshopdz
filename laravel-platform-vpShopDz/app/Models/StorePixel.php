<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StorePixel extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'store_id',
        'platform',
        'pixel_id',
        'name',
        'access_token',
        'is_active',
        'tracked_events',
        'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'tracked_events' => 'array',
        'settings' => 'array',
    ];

    protected $hidden = [
        'access_token',
    ];

    const PLATFORMS = [
        'facebook' => 'Facebook Pixel',
        'tiktok' => 'TikTok Pixel',
        'snapchat' => 'Snapchat Pixel',
        'google_analytics' => 'Google Analytics',
        'google_ads' => 'Google Ads',
        'twitter' => 'Twitter Pixel',
        'pinterest' => 'Pinterest Tag',
        'custom' => 'Custom Script',
    ];

    const DEFAULT_EVENTS = [
        'page_view',
        'view_content',
        'add_to_cart',
        'initiate_checkout',
        'purchase',
        'search',
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

    public function scopeForPlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    public function scopeForStore($query, string $storeId)
    {
        return $query->where('store_id', $storeId);
    }

    // ═══════════════════════════════════════════════════════════════
    // HELPERS
    // ═══════════════════════════════════════════════════════════════

    public function getPlatformNameAttribute(): string
    {
        return self::PLATFORMS[$this->platform] ?? $this->platform;
    }

    public function shouldTrackEvent(string $event): bool
    {
        $events = $this->tracked_events ?? self::DEFAULT_EVENTS;
        return in_array($event, $events);
    }

    /**
     * Generate pixel script for storefront
     */
    public function generateScript(): string
    {
        return match($this->platform) {
            'facebook' => $this->facebookPixelScript(),
            'tiktok' => $this->tiktokPixelScript(),
            'google_analytics' => $this->googleAnalyticsScript(),
            'google_ads' => $this->googleAdsScript(),
            'snapchat' => $this->snapchatPixelScript(),
            default => '',
        };
    }

    private function facebookPixelScript(): string
    {
        return "<!-- Facebook Pixel -->
<script>
!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
document,'script','https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '{$this->pixel_id}');
fbq('track', 'PageView');
</script>
<noscript><img height=\"1\" width=\"1\" style=\"display:none\"
src=\"https://www.facebook.com/tr?id={$this->pixel_id}&ev=PageView&noscript=1\"/></noscript>";
    }

    private function tiktokPixelScript(): string
    {
        return "<!-- TikTok Pixel -->
<script>
!function (w, d, t) {w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];
ttq.methods=['page','track','identify','instances','debug','on','off','once','ready','alias','group',
'enableCookie','disableCookie'];ttq.setAndDefer=function(t,e){t[e]=function(){
t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)
ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;
n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};ttq.load=function(e,n){
var i='https://analytics.tiktok.com/i18n/pixel/events.js';ttq._i=ttq._i||{};ttq._i[e]=[];
ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e+\"_\"+n]=1;var o=document.createElement('script');
o.type='text/javascript';o.async=!0;o.src=i+'?sdkid='+e+'&lib='+t;var a=document.getElementsByTagName('script')[0];
a.parentNode.insertBefore(o,a)};ttq.load('{$this->pixel_id}');ttq.page();}(window, document, 'ttq');
</script>";
    }

    private function googleAnalyticsScript(): string
    {
        return "<!-- Google Analytics -->
<script async src=\"https://www.googletagmanager.com/gtag/js?id={$this->pixel_id}\"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$this->pixel_id}');
</script>";
    }

    private function googleAdsScript(): string
    {
        return "<!-- Google Ads -->
<script async src=\"https://www.googletagmanager.com/gtag/js?id={$this->pixel_id}\"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '{$this->pixel_id}');
</script>";
    }

    private function snapchatPixelScript(): string
    {
        return "<!-- Snapchat Pixel -->
<script type='text/javascript'>
(function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function(){a.handleRequest?
a.handleRequest.apply(a,arguments):a.queue.push(arguments)};a.queue=[];
var s='script';r=t.createElement(s);r.async=!0;r.src=n;
var u=t.getElementsByTagName(s)[0];u.parentNode.insertBefore(r,u);
})(window,document,'https://sc-static.net/scevent.min.js');
snaptr('init', '{$this->pixel_id}', {});
snaptr('track', 'PAGE_VIEW');
</script>";
    }
}
