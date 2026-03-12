<!DOCTYPE html>
<html lang="{{ $store->default_language ?? $store->language ?? 'ar' }}" dir="{{ ($store->default_language ?? $store->language ?? 'ar') === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="{{ $store->theme_color ?? '#2563eb' }}">
    <meta name="description" content="{{ $store->description ?? '' }}">

    {{-- SEO Meta --}}
    <meta property="og:title" content="{{ $store->name }}">
    <meta property="og:description" content="{{ $store->description ?? '' }}">
    <meta property="og:type" content="website">
    @if($store->logo)
    <meta property="og:image" content="{{ asset('storage/' . $store->logo) }}">
    @endif

    <title>{{ $store->name }}</title>

    {{-- Favicon --}}
    @if($store->favicon)
        <link rel="icon" href="{{ asset('storage/' . $store->favicon) }}">
    @elseif($store->logo)
        <link rel="icon" href="{{ $store->logo }}" type="image/png">
    @endif

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">

    {{-- Theme Colors as CSS Variables --}}
    @php
        $themeColors = $themeSettings['colors'] ?? ($theme ? $theme->default_colors : []);
    @endphp
    <style>
        :root {
            --color-primary: {{ $themeColors['primary'] ?? '#2563eb' }};
            --color-secondary: {{ $themeColors['secondary'] ?? '#64748b' }};
            --color-accent: {{ $themeColors['accent'] ?? '#f59e0b' }};
            --color-background: {{ $themeColors['background'] ?? '#ffffff' }};
            --color-foreground: {{ $themeColors['foreground'] ?? $themeColors['text'] ?? '#1e293b' }};
            --color-card: {{ $themeColors['card'] ?? '#ffffff' }};
            --color-muted: {{ $themeColors['muted'] ?? '#f1f5f9' }};
            --color-border: {{ $themeColors['border'] ?? '#e2e8f0' }};
            --color-header-bg: {{ $themeColors['headerBg'] ?? $themeColors['header_bg'] ?? '#ffffff' }};
            --color-header-text: {{ $themeColors['headerText'] ?? '#1e293b' }};
            --color-footer-bg: {{ $themeColors['footerBg'] ?? $themeColors['footer_bg'] ?? '#1e293b' }};
            --color-footer-text: {{ $themeColors['footerText'] ?? '#f8fafc' }};
            --button-radius: {{ $themeColors['buttonRadius'] ?? '0.5rem' }};
            --card-radius: {{ $themeColors['cardRadius'] ?? '0.75rem' }};
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
                    ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e+"_"+n]=1;
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

    {{-- Store data for React --}}
    @php
        $storeData = [
            'id' => $store->id,
            'name' => $store->name,
            'slug' => $store->slug,
            'logo' => $store->logo,
            'favicon' => $store->favicon ?? null,
            'description' => $store->description,
            'language' => $store->language ?? 'ar',
            'currency' => $store->currency ?? 'DZD',
            'theme' => [
                'slug' => $theme?->slug ?? 'dawn',
                'colors' => $themeColors,
                'fonts' => $themeSettings['fonts'] ?? [],
                'layout' => $themeSettings['layout'] ?? [],
            ],
            'settings' => is_string($store->settings) ? json_decode($store->settings, true) : ($store->settings ?? []),
            'social_links' => [
                'facebook' => $store->facebook_url ?? null,
                'instagram' => $store->instagram_url ?? null,
                'whatsapp' => $store->whatsapp ?? null,
                'tiktok' => $store->tiktok_url ?? null,
            ],
            'contact' => [
                'phone' => $store->phone ?? null,
                'email' => $store->email ?? null,
                'address' => $store->address ?? null,
            ],
        ];
    @endphp
    <script>
        window.__STORE_DATA__ = {!! json_encode($storeData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!};
        window.__API_BASE__ = '{{ url("/api/v1/store/" . $store->slug) }}';
    </script>

    {{-- React Storefront Assets --}}
    @php
        $manifestPath = public_path('storefront/.vite/manifest.json');
        $manifest = file_exists($manifestPath)
            ? json_decode(file_get_contents($manifestPath), true)
            : null;
        // Find the entry point - could be 'main.tsx' or 'index.html'
        $entry = null;
        if ($manifest) {
            // Look for isEntry: true
            foreach ($manifest as $key => $item) {
                if (isset($item['isEntry']) && $item['isEntry']) {
                    $entry = $item;
                    break;
                }
            }
            // Fallback to common keys
            if (!$entry) {
                $entry = $manifest['main.tsx'] ?? $manifest['index.html'] ?? null;
            }
        }
    @endphp
    {{-- Tailwind CSS (built separately via CLI) --}}
    @if(file_exists(public_path('storefront/storefront.css')))
        <link rel="stylesheet" href="{{ asset('storefront/storefront.css') }}?v={{ filemtime(public_path('storefront/storefront.css')) }}">
    @endif

    @if($entry)
        {{-- Load pre-built storefront assets (use relative path to avoid http/https mismatch) --}}
        @if(isset($entry['css']))
            @foreach($entry['css'] as $css)
                <link rel="stylesheet" href="/storefront/{{ $css }}">
            @endforeach
        @endif
        <script type="module" src="/storefront/{{ $entry['file'] }}"></script>
    @else
        {{-- Fallback: try Vite dev server --}}
        <script type="module" src="http://localhost:5174/@vite/client"></script>
        <script type="module" src="http://localhost:5174/main.tsx"></script>
    @endif
</head>
<body>
    <div id="root"></div>
    <div id="storefront-root"></div>

    <noscript>
        <div style="text-align:center;padding:50px;font-family:Tajawal,sans-serif;">
            <h1>{{ $store->name }}</h1>
            <p>يرجى تفعيل JavaScript لعرض المتجر</p>
        </div>
    </noscript>
</body>
</html>
