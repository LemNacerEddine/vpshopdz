<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'لوحة التحكم') - {{ config('app.name') }}</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Tajawal Font -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <style>
        body { font-family: 'Tajawal', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen">
    <div x-data="{ sidebarOpen: true }" class="flex">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="bg-emerald-800 min-h-screen text-white transition-all duration-300 fixed right-0 top-0 z-50">
            <!-- Logo -->
            <div class="p-4 border-b border-emerald-700">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <span x-show="sidebarOpen" class="font-bold text-lg">{{ auth()->user()->store->name ?? 'VPShopDZ' }}</span>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav class="p-4 space-y-2">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-emerald-700 transition {{ request()->routeIs('dashboard') && !request()->routeIs('dashboard.*') ? 'bg-emerald-700' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    <span x-show="sidebarOpen">الرئيسية</span>
                </a>
                
                <a href="{{ route('dashboard.products') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-emerald-700 transition {{ request()->routeIs('dashboard.products*') ? 'bg-emerald-700' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <span x-show="sidebarOpen">المنتجات</span>
                </a>
                
                <a href="{{ route('dashboard.orders') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-emerald-700 transition {{ request()->routeIs('dashboard.orders*') ? 'bg-emerald-700' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                    </svg>
                    <span x-show="sidebarOpen">الطلبات</span>
                    @if(($pendingOrders ?? 0) > 0)
                        <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full">{{ $pendingOrders }}</span>
                    @endif
                </a>
                
                <a href="{{ route('dashboard.settings') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-emerald-700 transition {{ request()->routeIs('dashboard.settings*') ? 'bg-emerald-700' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span x-show="sidebarOpen">الإعدادات</span>
                </a>
                
                <hr class="border-emerald-700 my-4">
                
                <a href="/store/{{ auth()->user()->store->slug ?? '' }}" target="_blank" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-emerald-700 transition">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                    <span x-show="sidebarOpen">زيارة المتجر</span>
                </a>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-red-600 transition w-full text-right">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span x-show="sidebarOpen">تسجيل الخروج</span>
                    </button>
                </form>
            </nav>
            
            <!-- Toggle Button -->
            <button @click="sidebarOpen = !sidebarOpen" class="absolute -left-3 top-20 bg-emerald-600 rounded-full p-1 shadow-lg">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" :class="sidebarOpen ? 'rotate-180' : ''">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
            </button>
        </aside>
        
        <!-- Main Content -->
        <main :class="sidebarOpen ? 'mr-64' : 'mr-20'" class="flex-1 transition-all duration-300">
            <!-- Top Bar -->
            <header class="bg-white shadow-sm px-6 py-4 sticky top-0 z-40">
                <div class="flex items-center justify-between">
                    <h1 class="text-xl font-bold text-gray-800">@yield('title', 'لوحة التحكم')</h1>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-500">مرحباً، {{ auth()->user()->name }}</span>
                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                            <span class="text-emerald-600 font-bold">{{ mb_substr(auth()->user()->name, 0, 1) }}</span>
                        </div>
                    </div>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                
                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                        {{ session('error') }}
                    </div>
                @endif
                
                @yield('content')
            </div>
        </main>
    </div>
    
    @stack('scripts')
</body>
</html>
