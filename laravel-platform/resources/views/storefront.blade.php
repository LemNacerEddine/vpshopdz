<!DOCTYPE html>
<html lang="{{ $store->language ?? 'ar' }}" dir="{{ ($store->language ?? 'ar') === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="{{ $store->description }}">

    <title>{{ $store->name }}</title>

    @if($store->logo)
        <link rel="icon" href="{{ $store->logo }}" type="image/png">
    @endif

    {{-- Theme Colors as CSS Variables --}}
    @php
        $colors = $themeSettings['colors'] ?? ($theme ? $theme->default_colors : []);
    @endphp
    <style>
        :root {
            --color-primary: {{ $colors['primary'] ?? '#2563eb' }};
            --color-secondary: {{ $colors['secondary'] ?? '#64748b' }};
            --color-accent: {{ $colors['accent'] ?? '#f59e0b' }};
            --color-background: {{ $colors['background'] ?? '#ffffff' }};
            --color-text: {{ $colors['text'] ?? '#1e293b' }};
            --color-header-bg: {{ $colors['header_bg'] ?? '#ffffff' }};
            --color-footer-bg: {{ $colors['footer_bg'] ?? '#1e293b' }};
        }
    </style>

    {{-- Tracking Pixels --}}
    @foreach($store->pixels()->where('is_active', true)->get() as $pixel)
        @if($pixel->type === 'facebook')
            <!-- Facebook Pixel -->
            <script>
                !function(f,b,e,v,n,t,s)
                {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};
                if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
                n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t,s)}(window, document,'script',
                'https://connect.facebook.net/en_US/fbevents.js');
                fbq('init', '{{ $pixel->pixel_id }}');
                fbq('track', 'PageView');
            </script>
        @elseif($pixel->type === 'google_analytics')
            <!-- Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $pixel->pixel_id }}"></script>
            <script>
                window.dataLayer = window.dataLayer || [];
                function gtag(){dataLayer.push(arguments);}
                gtag('js', new Date());
                gtag('config', '{{ $pixel->pixel_id }}');
            </script>
        @elseif($pixel->type === 'tiktok')
            <!-- TikTok Pixel -->
            <script>
                !function (w, d, t) {
                    w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];
                    ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];
                    ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};
                    for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);
                    ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};
                    ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";
                    ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e+\"_\"+n]=1;
                    var o=document.createElement("script");o.type="text/javascript";o.async=!0;o.src=i+"?sdkid="+e+"&lib="+t;
                    var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
                    ttq.load('{{ $pixel->pixel_id }}');
                    ttq.page();
                }(window, document, 'ttq');
            </script>
        @elseif($pixel->type === 'snapchat')
            <!-- Snapchat Pixel -->
            <script>
                (function(e,t,n){if(e.snaptr)return;var a=e.snaptr=function()
                {a.handleRequest?a.handleRequest.apply(a,arguments):a.queue.push(arguments)};
                a.queue=[];var s='script';r=t.createElement(s);r.async=!0;
                r.src=n;var u=t.getElementsByTagName(s)[0];
                u.parentNode.insertBefore(r,u);})(window,document,
                'https://sc-static.net/scevent.min.js');
                snaptr('init', '{{ $pixel->pixel_id }}');
                snaptr('track', 'PAGE_VIEW');
            </script>
        @endif
    @endforeach

    {{-- React Storefront will be loaded here --}}
    @viteReactRefresh
    @vite(['resources/storefront/main.tsx'])
</head>
<body>
    <div id="storefront-root"></div>

    {{-- Store data for React --}}
    <script>
        window.__STORE_DATA__ = {!! $storeJson !!};
        window.__API_BASE__ = '{{ url("/api/v1/store/" . $store->slug) }}';
    </script>
</body>
</html>
