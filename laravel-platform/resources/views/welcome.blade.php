<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="VPShopDZ - أقوى منصة جزائرية لإنشاء متجرك الإلكتروني. 0% عمولة، أسعار لا تُقاوم، ميزات حصرية. ابدأ مجاناً الآن!">
    <meta name="keywords" content="متجر الكتروني, التجارة الإلكترونية, الجزائر, إنشاء متجر, بيع أونلاين">
    <title>VPShopDZ | أنشئ متجرك الإلكتروني - المنصة الجزائرية الأولى</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7',
                            400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857',
                            800: '#065f46', 900: '#064e3b',
                        }
                    },
                    fontFamily: { 'tajawal': ['Tajawal', 'sans-serif'] }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    
    <style>
        * { font-family: 'Tajawal', sans-serif; }
        [x-cloak] { display: none !important; }
        
        .hero-gradient {
            background: linear-gradient(135deg, #064e3b 0%, #065f46 25%, #047857 50%, #059669 75%, #10b981 100%);
        }
        
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
        }
        
        .floating {
            animation: floating 6s ease-in-out infinite;
        }
        
        @keyframes floating {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }
        
        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite;
        }
        
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(16, 185, 129, 0.4); }
            50% { box-shadow: 0 0 40px rgba(16, 185, 129, 0.8); }
        }
        
        .card-hover {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        
        .card-hover:hover {
            transform: translateY(-10px) scale(1.02);
        }
        
        .gradient-text {
            background: linear-gradient(135deg, #10b981 0%, #34d399 50%, #6ee7b7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .blob {
            border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%;
            animation: blob 8s ease-in-out infinite;
        }
        
        @keyframes blob {
            0%, 100% { border-radius: 30% 70% 70% 30% / 30% 30% 70% 70%; }
            50% { border-radius: 70% 30% 30% 70% / 70% 70% 30% 30%; }
        }
        
        .marquee {
            animation: marquee 30s linear infinite;
        }
        
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        
        /* Smooth Scroll */
        html { scroll-behavior: smooth; }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #10b981; border-radius: 4px; }
    </style>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-white" x-data="{ mobileMenu: false, faqOpen: null }">
    
    <!-- Navigation -->
    <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300" 
         x-data="{ scrolled: false }"
         @scroll.window="scrolled = (window.pageYOffset > 50)"
         :class="scrolled ? 'bg-white/95 backdrop-blur-lg shadow-lg' : 'bg-transparent'">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="#" class="flex items-center gap-3" data-testid="nav-logo">
                    <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                        <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-black" :class="scrolled ? 'text-gray-800' : 'text-white'">VPShop<span class="text-primary-500">DZ</span></span>
                </a>
                
                <!-- Desktop Menu -->
                <div class="hidden lg:flex items-center gap-8">
                    <a href="#features" class="font-medium transition-colors" :class="scrolled ? 'text-gray-600 hover:text-primary-600' : 'text-white/80 hover:text-white'" data-testid="nav-features">المميزات</a>
                    <a href="#how-it-works" class="font-medium transition-colors" :class="scrolled ? 'text-gray-600 hover:text-primary-600' : 'text-white/80 hover:text-white'" data-testid="nav-how">كيف تعمل</a>
                    <a href="#pricing" class="font-medium transition-colors" :class="scrolled ? 'text-gray-600 hover:text-primary-600' : 'text-white/80 hover:text-white'" data-testid="nav-pricing">الأسعار</a>
                    <a href="#faq" class="font-medium transition-colors" :class="scrolled ? 'text-gray-600 hover:text-primary-600' : 'text-white/80 hover:text-white'" data-testid="nav-faq">الأسئلة الشائعة</a>
                </div>
                
                <!-- CTA Buttons -->
                <div class="hidden lg:flex items-center gap-4">
                    <a href="{{ route('login') }}" class="font-medium transition-colors" :class="scrolled ? 'text-gray-600 hover:text-primary-600' : 'text-white/80 hover:text-white'" data-testid="nav-login">تسجيل الدخول</a>
                    <a href="{{ route('register') }}" class="bg-white text-primary-600 px-6 py-3 rounded-xl font-bold hover:bg-primary-50 transition-all shadow-lg" data-testid="nav-register">
                        ابدأ مجاناً
                    </a>
                </div>
                
                <!-- Mobile Menu Button -->
                <button @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 rounded-xl" :class="scrolled ? 'text-gray-800' : 'text-white'" data-testid="mobile-menu-btn">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path x-show="!mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        <path x-show="mobileMenu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div x-show="mobileMenu" x-transition class="lg:hidden bg-white border-t">
            <div class="px-4 py-6 space-y-4">
                <a href="#features" @click="mobileMenu = false" class="block text-gray-600 hover:text-primary-600 font-medium py-2">المميزات</a>
                <a href="#how-it-works" @click="mobileMenu = false" class="block text-gray-600 hover:text-primary-600 font-medium py-2">كيف تعمل</a>
                <a href="#pricing" @click="mobileMenu = false" class="block text-gray-600 hover:text-primary-600 font-medium py-2">الأسعار</a>
                <a href="#faq" @click="mobileMenu = false" class="block text-gray-600 hover:text-primary-600 font-medium py-2">الأسئلة الشائعة</a>
                <div class="pt-4 border-t space-y-3">
                    <a href="{{ route('login') }}" class="block text-center text-gray-600 font-medium py-2">تسجيل الدخول</a>
                    <a href="{{ route('register') }}" class="block text-center bg-primary-600 text-white px-6 py-3 rounded-xl font-bold">ابدأ مجاناً</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-gradient min-h-screen relative overflow-hidden pt-20">
        <!-- Background Elements -->
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-20 right-10 w-96 h-96 bg-white/5 rounded-full blur-3xl blob"></div>
            <div class="absolute bottom-20 left-10 w-80 h-80 bg-primary-300/10 rounded-full blur-3xl blob" style="animation-delay: 2s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-primary-400/5 rounded-full blur-3xl"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-32 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Hero Content -->
                <div class="text-center lg:text-right">
                    <!-- Badge -->
                    <div class="inline-flex items-center gap-2 bg-white/10 backdrop-blur-sm px-4 py-2 rounded-full mb-8">
                        <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                        <span class="text-white/90 text-sm font-medium">المنصة رقم #1 في الجزائر</span>
                    </div>
                    
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white leading-tight mb-6">
                        أنشئ متجرك الإلكتروني
                        <br>
                        <span class="text-primary-300">في دقائق معدودة</span>
                    </h1>
                    
                    <p class="text-xl text-white/80 mb-8 max-w-xl mx-auto lg:mx-0">
                        المنصة الجزائرية الأقوى والأرخص لإنشاء متجرك الإلكتروني. 
                        <span class="text-primary-300 font-bold">0% عمولة</span> على مبيعاتك، 
                        أدوات احترافية، ودعم فني متواصل.
                    </p>
                    
                    <!-- Stats -->
                    <div class="flex flex-wrap justify-center lg:justify-start gap-8 mb-10">
                        <div class="text-center">
                            <p class="text-4xl font-black text-white">+850</p>
                            <p class="text-white/60 text-sm">تاجر نشط</p>
                        </div>
                        <div class="text-center">
                            <p class="text-4xl font-black text-white">+12,000</p>
                            <p class="text-white/60 text-sm">طلب شهرياً</p>
                        </div>
                        <div class="text-center">
                            <p class="text-4xl font-black text-white">58</p>
                            <p class="text-white/60 text-sm">ولاية مدعومة</p>
                        </div>
                    </div>
                    
                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <a href="{{ route('register') }}" class="bg-white text-primary-600 px-8 py-4 rounded-2xl font-bold text-lg hover:bg-primary-50 transition-all shadow-2xl shadow-black/20 pulse-glow flex items-center justify-center gap-2" data-testid="hero-cta">
                            <span>ابدأ مجاناً الآن</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                        <a href="#how-it-works" class="glass text-white px-8 py-4 rounded-2xl font-bold text-lg hover:bg-white/20 transition-all flex items-center justify-center gap-2" data-testid="hero-demo">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>شاهد كيف تعمل</span>
                        </a>
                    </div>
                    
                    <!-- Trust Badges -->
                    <div class="mt-10 flex flex-wrap items-center justify-center lg:justify-start gap-6 text-white/60 text-sm">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                            </svg>
                            <span>آمن 100%</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>0% عمولة</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                            <span>سريع جداً</span>
                        </div>
                    </div>
                </div>
                
                <!-- Hero Image/Dashboard Preview -->
                <div class="relative hidden lg:block">
                    <div class="relative floating">
                        <div class="bg-white rounded-3xl shadow-2xl p-4 transform rotate-2">
                            <div class="bg-gray-100 rounded-2xl aspect-video flex items-center justify-center">
                                <div class="text-center p-8">
                                    <div class="w-20 h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-3xl flex items-center justify-center mx-auto mb-4 shadow-lg">
                                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-600 font-bold">لوحة تحكم احترافية</p>
                                    <p class="text-gray-400 text-sm">إدارة متجرك بسهولة</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Cards -->
                        <div class="absolute -top-10 -right-10 bg-white rounded-2xl shadow-xl p-4 transform -rotate-6" style="animation: floating 4s ease-in-out infinite; animation-delay: 0.5s;">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800 text-sm">طلب جديد!</p>
                                    <p class="text-xs text-gray-500">4,500 د.ج</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="absolute -bottom-5 -left-10 bg-white rounded-2xl shadow-xl p-4 transform rotate-6" style="animation: floating 5s ease-in-out infinite; animation-delay: 1s;">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center">
                                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-800 text-sm">+23% مبيعات</p>
                                    <p class="text-xs text-gray-500">هذا الأسبوع</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Wave -->
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 120L60 110C120 100 240 80 360 70C480 60 600 60 720 65C840 70 960 80 1080 85C1200 90 1320 90 1380 90L1440 90V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
            </svg>
        </div>
    </section>

    <!-- Partners/Brands Marquee -->
    <section class="py-12 bg-gray-50 overflow-hidden">
        <div class="text-center mb-8">
            <p class="text-gray-500 font-medium">شركات التوصيل المتكاملة مع VPShopDZ</p>
        </div>
        <div class="relative">
            <div class="flex gap-16 items-center marquee">
                @foreach(['Yalidine', 'ZR Express', 'Ecotrack', 'Procolis', 'Maystro', 'DHD', 'E-Com', 'Guepex', 'Yalidine', 'ZR Express', 'Ecotrack', 'Procolis', 'Maystro', 'DHD'] as $company)
                    <div class="flex-shrink-0 px-8 py-4 bg-white rounded-xl shadow-sm">
                        <span class="text-xl font-bold text-gray-400">{{ $company }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block px-4 py-2 bg-primary-100 text-primary-600 rounded-full text-sm font-bold mb-4">سهل وسريع</span>
                <h2 class="text-4xl lg:text-5xl font-black text-gray-800 mb-4">كيف تعمل المنصة؟</h2>
                <p class="text-xl text-gray-500 max-w-2xl mx-auto">في 4 خطوات بسيطة فقط، أطلق متجرك الإلكتروني وابدأ البيع</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-8">
                <!-- Step 1 -->
                <div class="relative text-center group">
                    <div class="w-20 h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-primary-500/30 group-hover:scale-110 transition-transform">
                        <span class="text-3xl font-black text-white">1</span>
                    </div>
                    <div class="hidden lg:block absolute top-10 left-0 w-full h-0.5 bg-gradient-to-l from-primary-300 to-transparent -z-10"></div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">أنشئ حسابك</h3>
                    <p class="text-gray-500">سجّل مجاناً في أقل من دقيقة واحدة</p>
                </div>
                
                <!-- Step 2 -->
                <div class="relative text-center group">
                    <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-blue-500/30 group-hover:scale-110 transition-transform">
                        <span class="text-3xl font-black text-white">2</span>
                    </div>
                    <div class="hidden lg:block absolute top-10 left-0 w-full h-0.5 bg-gradient-to-l from-blue-300 to-transparent -z-10"></div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">أضف منتجاتك</h3>
                    <p class="text-gray-500">أضف منتجاتك بالصور والأسعار والوصف</p>
                </div>
                
                <!-- Step 3 -->
                <div class="relative text-center group">
                    <div class="w-20 h-20 bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-purple-500/30 group-hover:scale-110 transition-transform">
                        <span class="text-3xl font-black text-white">3</span>
                    </div>
                    <div class="hidden lg:block absolute top-10 left-0 w-full h-0.5 bg-gradient-to-l from-purple-300 to-transparent -z-10"></div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">استلم الطلبات</h3>
                    <p class="text-gray-500">طلبات جديدة تصلك فوراً مع إشعارات</p>
                </div>
                
                <!-- Step 4 -->
                <div class="relative text-center group">
                    <div class="w-20 h-20 bg-gradient-to-br from-amber-500 to-amber-600 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-xl shadow-amber-500/30 group-hover:scale-110 transition-transform">
                        <span class="text-3xl font-black text-white">4</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">وصّل واربح</h3>
                    <p class="text-gray-500">أرسل للتوصيل واستلم أرباحك</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block px-4 py-2 bg-primary-100 text-primary-600 rounded-full text-sm font-bold mb-4">ميزات حصرية</span>
                <h2 class="text-4xl lg:text-5xl font-black text-gray-800 mb-4">لماذا VPShopDZ؟</h2>
                <p class="text-xl text-gray-500 max-w-2xl mx-auto">ميزات لن تجدها في أي منصة أخرى، صُممت خصيصاً للتاجر الجزائري</p>
            </div>
            
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="bg-white rounded-3xl p-8 shadow-xl shadow-gray-100 card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-green-400 to-green-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-green-500/30">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">0% عمولة</h3>
                    <p class="text-gray-500 leading-relaxed">نحن لا نأخذ أي عمولة على مبيعاتك. كل أرباحك لك 100%، فقط اشتراك شهري رمزي.</p>
                </div>
                
                <!-- Feature 2 -->
                <div class="bg-white rounded-3xl p-8 shadow-xl shadow-gray-100 card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-blue-500/30">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">58 ولاية + 1011 بلدية</h3>
                    <p class="text-gray-500 leading-relaxed">تغطية شاملة لكل الجزائر. أسعار الشحن محدثة تلقائياً لكل بلدية.</p>
                </div>
                
                <!-- Feature 3 -->
                <div class="bg-white rounded-3xl p-8 shadow-xl shadow-gray-100 card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-purple-500/30">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">سرعة خارقة</h3>
                    <p class="text-gray-500 leading-relaxed">متجرك سريع البرق. صفحات تفتح في أقل من ثانية = عملاء أكثر ومبيعات أعلى.</p>
                </div>
                
                <!-- Feature 4 -->
                <div class="bg-white rounded-3xl p-8 shadow-xl shadow-gray-100 card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-pink-400 to-pink-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-pink-500/30">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Facebook & TikTok Pixel</h3>
                    <p class="text-gray-500 leading-relaxed">ربط مباشر مع بيكسلات فيسبوك وتيكتوك. تتبع إعلاناتك وحسّن حملاتك.</p>
                </div>
                
                <!-- Feature 5 -->
                <div class="bg-white rounded-3xl p-8 shadow-xl shadow-gray-100 card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-amber-400 to-amber-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-amber-500/30">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">ربط شركات التوصيل</h3>
                    <p class="text-gray-500 leading-relaxed">تكامل تلقائي مع Yalidine وZR Express وEcotrack وغيرها. أرسل طلباتك بنقرة واحدة.</p>
                </div>
                
                <!-- Feature 6 -->
                <div class="bg-white rounded-3xl p-8 shadow-xl shadow-gray-100 card-hover border border-gray-100">
                    <div class="w-16 h-16 bg-gradient-to-br from-cyan-400 to-cyan-600 rounded-2xl flex items-center justify-center mb-6 shadow-lg shadow-cyan-500/30">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">متجاوب 100%</h3>
                    <p class="text-gray-500 leading-relaxed">متجرك يظهر بشكل مثالي على الموبايل والتابلت والكمبيوتر. تجربة ممتازة لكل عملائك.</p>
                </div>
            </div>
            
            <!-- Extra Features -->
            <div class="mt-16 bg-gradient-to-l from-primary-600 to-primary-700 rounded-3xl p-8 lg:p-12">
                <div class="grid lg:grid-cols-2 gap-8 items-center">
                    <div>
                        <h3 class="text-3xl font-black text-white mb-4">وميزات أخرى كثيرة...</h3>
                        <ul class="space-y-3">
                            <li class="flex items-center gap-3 text-white/90">
                                <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>تصاميم متجر متعددة وقابلة للتخصيص</span>
                            </li>
                            <li class="flex items-center gap-3 text-white/90">
                                <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>دومين مخصص (yourstore.com)</span>
                            </li>
                            <li class="flex items-center gap-3 text-white/90">
                                <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>إحصائيات وتقارير مفصلة</span>
                            </li>
                            <li class="flex items-center gap-3 text-white/90">
                                <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>إشعارات فورية للطلبات الجديدة</span>
                            </li>
                            <li class="flex items-center gap-3 text-white/90">
                                <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>إدارة المخزون التلقائية</span>
                            </li>
                            <li class="flex items-center gap-3 text-white/90">
                                <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <span>دعم فني على مدار الساعة</span>
                            </li>
                        </ul>
                    </div>
                    <div class="text-center">
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 bg-white text-primary-600 px-8 py-4 rounded-2xl font-bold text-lg hover:bg-primary-50 transition-all shadow-xl" data-testid="features-cta">
                            <span>جرّب كل هذا مجاناً</span>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block px-4 py-2 bg-primary-100 text-primary-600 rounded-full text-sm font-bold mb-4">أسعار لا تُقاوم</span>
                <h2 class="text-4xl lg:text-5xl font-black text-gray-800 mb-4">أسعار أقل بـ 50% من المنافسين</h2>
                <p class="text-xl text-gray-500 max-w-2xl mx-auto">اختر الخطة المناسبة لك. جميع الخطط تشمل 0% عمولة على المبيعات</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <!-- Starter Plan -->
                <div class="bg-white rounded-3xl border-2 border-gray-200 p-8 hover:border-primary-300 transition-all card-hover">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">المبتدئ</h3>
                        <p class="text-gray-500 mb-4">للتجار الجدد</p>
                        <div class="flex items-baseline justify-center gap-1">
                            <span class="text-5xl font-black text-gray-800">800</span>
                            <span class="text-xl text-gray-500">د.ج/شهر</span>
                        </div>
                    </div>
                    
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600"><strong>200</strong> طلب/شهر</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600">منتجات <strong>غير محدودة</strong></span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600"><strong>1</strong> Facebook Pixel</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600">ربط شركات التوصيل</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            <span class="text-gray-400">دومين مخصص</span>
                        </li>
                    </ul>
                    
                    <a href="{{ route('register') }}" class="block w-full text-center bg-gray-100 text-gray-800 px-6 py-4 rounded-xl font-bold hover:bg-gray-200 transition-all" data-testid="pricing-starter">
                        ابدأ الآن
                    </a>
                </div>
                
                <!-- Pro Plan - Popular -->
                <div class="bg-gradient-to-b from-primary-600 to-primary-700 rounded-3xl p-8 relative transform scale-105 shadow-2xl shadow-primary-500/30">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                        <span class="bg-amber-400 text-amber-900 px-4 py-1 rounded-full text-sm font-bold">الأكثر طلباً</span>
                    </div>
                    
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-white mb-2">المحترف</h3>
                        <p class="text-white/70 mb-4">للتجار النشطين</p>
                        <div class="flex items-baseline justify-center gap-1">
                            <span class="text-5xl font-black text-white">1,500</span>
                            <span class="text-xl text-white/70">د.ج/شهر</span>
                        </div>
                    </div>
                    
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white"><strong>500</strong> طلب/شهر</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white">منتجات <strong>غير محدودة</strong></span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white"><strong>3</strong> Facebook Pixels</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white"><strong>1</strong> TikTok Pixel</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white"><strong>1</strong> دومين مخصص</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-white"><strong>2</strong> عمّال متجر</span>
                        </li>
                    </ul>
                    
                    <a href="{{ route('register') }}" class="block w-full text-center bg-white text-primary-600 px-6 py-4 rounded-xl font-bold hover:bg-primary-50 transition-all" data-testid="pricing-pro">
                        ابدأ الآن
                    </a>
                </div>
                
                <!-- Enterprise Plan -->
                <div class="bg-white rounded-3xl border-2 border-gray-200 p-8 hover:border-primary-300 transition-all card-hover">
                    <div class="text-center mb-8">
                        <h3 class="text-2xl font-bold text-gray-800 mb-2">غير محدود</h3>
                        <p class="text-gray-500 mb-4">للتجار الكبار</p>
                        <div class="flex items-baseline justify-center gap-1">
                            <span class="text-5xl font-black text-gray-800">2,500</span>
                            <span class="text-xl text-gray-500">د.ج/شهر</span>
                        </div>
                    </div>
                    
                    <ul class="space-y-4 mb-8">
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600">طلبات <strong>غير محدودة</strong></span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600">منتجات <strong>غير محدودة</strong></span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600"><strong>10</strong> Facebook Pixels</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600"><strong>5</strong> TikTok Pixels</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600"><strong>3</strong> دومينات مخصصة</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span class="text-gray-600">عمّال <strong>غير محدود</strong></span>
                        </li>
                    </ul>
                    
                    <a href="{{ route('register') }}" class="block w-full text-center bg-gray-100 text-gray-800 px-6 py-4 rounded-xl font-bold hover:bg-gray-200 transition-all" data-testid="pricing-enterprise">
                        ابدأ الآن
                    </a>
                </div>
            </div>
            
            <!-- Free Trial Banner -->
            <div class="mt-16 bg-gradient-to-l from-amber-400 to-amber-500 rounded-3xl p-8 text-center">
                <div class="flex flex-col md:flex-row items-center justify-center gap-6">
                    <div class="flex items-center gap-4">
                        <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                            </svg>
                        </div>
                        <div class="text-right">
                            <p class="text-2xl font-black text-white">🎁 شهر كامل مجاناً!</p>
                            <p class="text-white/80">+ 50 طلب مجاني • بدون بطاقة ائتمان • إلغاء في أي وقت</p>
                        </div>
                    </div>
                    <a href="{{ route('register') }}" class="bg-white text-amber-600 px-8 py-4 rounded-xl font-bold hover:bg-amber-50 transition-all shadow-lg" data-testid="trial-cta">
                        جرّب مجاناً الآن
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-24 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block px-4 py-2 bg-primary-100 text-primary-600 rounded-full text-sm font-bold mb-4">آراء عملائنا</span>
                <h2 class="text-4xl lg:text-5xl font-black text-gray-800 mb-4">ماذا يقول تجارنا؟</h2>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-3xl p-8 shadow-xl card-hover">
                    <div class="flex items-center gap-1 mb-4">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">"أفضل منصة استخدمتها! السعر ممتاز جداً والدعم الفني سريع. حققت +300% زيادة في المبيعات خلال شهرين فقط."</p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                            <span class="text-primary-600 font-bold">أ</span>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">أحمد بن علي</p>
                            <p class="text-sm text-gray-500">صاحب متجر أزياء - الجزائر</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-3xl p-8 shadow-xl card-hover">
                    <div class="flex items-center gap-1 mb-4">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">"كنت أستخدم منصة أخرى وكانت تأخذ عمولة كبيرة. الآن مع VPShopDZ كل أرباحي لي! فعلاً 0% عمولة."</p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-pink-100 rounded-full flex items-center justify-center">
                            <span class="text-pink-600 font-bold">س</span>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">سارة محمودي</p>
                            <p class="text-sm text-gray-500">متجر مستحضرات تجميل - وهران</p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-3xl p-8 shadow-xl card-hover">
                    <div class="flex items-center gap-1 mb-4">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                            </svg>
                        @endfor
                    </div>
                    <p class="text-gray-600 mb-6 leading-relaxed">"ربط شركات التوصيل تلقائي وسهل جداً. أرسل طلباتي لـ Yalidine بنقرة واحدة. وفّرت ساعات من العمل!"</p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                            <span class="text-blue-600 font-bold">ي</span>
                        </div>
                        <div>
                            <p class="font-bold text-gray-800">ياسين بوعلام</p>
                            <p class="text-sm text-gray-500">متجر إلكترونيات - قسنطينة</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block px-4 py-2 bg-primary-100 text-primary-600 rounded-full text-sm font-bold mb-4">الأسئلة الشائعة</span>
                <h2 class="text-4xl lg:text-5xl font-black text-gray-800 mb-4">أسئلة متكررة</h2>
                <p class="text-xl text-gray-500">إجابات لأكثر الأسئلة شيوعاً</p>
            </div>
            
            <div class="space-y-4">
                <!-- FAQ 1 -->
                <div class="bg-gray-50 rounded-2xl overflow-hidden">
                    <button @click="faqOpen = faqOpen === 1 ? null : 1" class="w-full px-6 py-5 text-right flex items-center justify-between hover:bg-gray-100 transition-colors" data-testid="faq-1">
                        <span class="text-lg font-bold text-gray-800">هل يقدم VPShopDZ تجربة مجانية؟</span>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="faqOpen === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="faqOpen === 1" x-collapse class="px-6 pb-5">
                        <p class="text-gray-600">نعم! نقدم <strong>شهر كامل تجربة مجانية</strong> مع <strong>50 طلب مجاني</strong> بدون الحاجة لبطاقة ائتمان. جرّب كل ميزات المنصة قبل الاشتراك.</p>
                    </div>
                </div>
                
                <!-- FAQ 2 -->
                <div class="bg-gray-50 rounded-2xl overflow-hidden">
                    <button @click="faqOpen = faqOpen === 2 ? null : 2" class="w-full px-6 py-5 text-right flex items-center justify-between hover:bg-gray-100 transition-colors" data-testid="faq-2">
                        <span class="text-lg font-bold text-gray-800">هل تأخذون عمولة على المبيعات؟</span>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="faqOpen === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="faqOpen === 2" x-collapse class="px-6 pb-5">
                        <p class="text-gray-600"><strong>أبداً! 0% عمولة</strong> على كل مبيعاتك. نحن نؤمن أن أرباحك يجب أن تبقى لك. نموذج عملنا يعتمد فقط على الاشتراك الشهري الرمزي.</p>
                    </div>
                </div>
                
                <!-- FAQ 3 -->
                <div class="bg-gray-50 rounded-2xl overflow-hidden">
                    <button @click="faqOpen = faqOpen === 3 ? null : 3" class="w-full px-6 py-5 text-right flex items-center justify-between hover:bg-gray-100 transition-colors" data-testid="faq-3">
                        <span class="text-lg font-bold text-gray-800">كيف يمكنني الدفع؟</span>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="faqOpen === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="faqOpen === 3" x-collapse class="px-6 pb-5">
                        <p class="text-gray-600">نقبل الدفع عبر: <strong>CCP</strong>، <strong>بريدي موب</strong>، <strong>EDAHABIA</strong>، و<strong>التحويل البنكي</strong>. كل طرق الدفع المحلية متاحة لراحتك.</p>
                    </div>
                </div>
                
                <!-- FAQ 4 -->
                <div class="bg-gray-50 rounded-2xl overflow-hidden">
                    <button @click="faqOpen = faqOpen === 4 ? null : 4" class="w-full px-6 py-5 text-right flex items-center justify-between hover:bg-gray-100 transition-colors" data-testid="faq-4">
                        <span class="text-lg font-bold text-gray-800">هل أحتاج خبرة في البرمجة؟</span>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="faqOpen === 4 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="faqOpen === 4" x-collapse class="px-6 pb-5">
                        <p class="text-gray-600"><strong>لا، أبداً!</strong> المنصة مصممة لتكون سهلة الاستخدام لأي شخص. أنشئ متجرك في دقائق بدون أي معرفة تقنية. نحن نتولى كل الأمور التقنية.</p>
                    </div>
                </div>
                
                <!-- FAQ 5 -->
                <div class="bg-gray-50 rounded-2xl overflow-hidden">
                    <button @click="faqOpen = faqOpen === 5 ? null : 5" class="w-full px-6 py-5 text-right flex items-center justify-between hover:bg-gray-100 transition-colors" data-testid="faq-5">
                        <span class="text-lg font-bold text-gray-800">هل بياناتي آمنة؟</span>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="faqOpen === 5 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="faqOpen === 5" x-collapse class="px-6 pb-5">
                        <p class="text-gray-600"><strong>نعم، 100%!</strong> نستخدم تشفير SSL وأحدث تقنيات الأمان لحماية بياناتك ومبيعاتك. بياناتك ملكك وحدك ولن نشاركها مع أي طرف.</p>
                    </div>
                </div>
                
                <!-- FAQ 6 -->
                <div class="bg-gray-50 rounded-2xl overflow-hidden">
                    <button @click="faqOpen = faqOpen === 6 ? null : 6" class="w-full px-6 py-5 text-right flex items-center justify-between hover:bg-gray-100 transition-colors" data-testid="faq-6">
                        <span class="text-lg font-bold text-gray-800">ما الفرق بينكم وبين المنصات الأخرى؟</span>
                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="faqOpen === 6 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div x-show="faqOpen === 6" x-collapse class="px-6 pb-5">
                        <p class="text-gray-600">نتميز بـ: <strong>أسعار أقل بـ 50%</strong>، <strong>0% عمولة</strong>، <strong>سرعة فائقة</strong>، <strong>تكامل مجاني مع كل شركات التوصيل</strong>، و<strong>دعم فني 24/7</strong>. كل هذا مع واجهة أسهل وأجمل.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Final CTA -->
    <section class="py-24 hero-gradient relative overflow-hidden">
        <div class="absolute inset-0 overflow-hidden">
            <div class="absolute top-20 right-10 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-20 left-10 w-80 h-80 bg-primary-300/10 rounded-full blur-3xl"></div>
        </div>
        
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <h2 class="text-4xl lg:text-5xl font-black text-white mb-6">جاهز لإطلاق متجرك؟</h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                انضم إلى +10,000 تاجر جزائري يستخدمون VPShopDZ. ابدأ مجاناً اليوم وشاهد الفرق!
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('register') }}" class="bg-white text-primary-600 px-10 py-5 rounded-2xl font-bold text-xl hover:bg-primary-50 transition-all shadow-2xl shadow-black/20 pulse-glow flex items-center justify-center gap-3" data-testid="final-cta">
                    <span>أنشئ متجرك مجاناً</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </a>
            </div>
            <p class="text-white/60 mt-6 text-sm">7 أيام مجاناً • بدون بطاقة ائتمان • إلغاء في أي وقت</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-12 mb-12">
                <!-- Brand -->
                <div>
                    <div class="flex items-center gap-3 mb-6">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-primary-600 rounded-2xl flex items-center justify-center">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <span class="text-2xl font-black">VPShop<span class="text-primary-400">DZ</span></span>
                    </div>
                    <p class="text-gray-400 mb-6">المنصة الجزائرية الأولى لإنشاء المتاجر الإلكترونية. أسعار لا تُقاوم وميزات حصرية.</p>
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-xl flex items-center justify-center hover:bg-primary-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        <a href="#" class="w-10 h-10 bg-gray-800 rounded-xl flex items-center justify-center hover:bg-primary-600 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                
                <!-- Links -->
                <div>
                    <h4 class="font-bold text-lg mb-6">روابط سريعة</h4>
                    <ul class="space-y-3">
                        <li><a href="#features" class="text-gray-400 hover:text-white transition-colors">المميزات</a></li>
                        <li><a href="#pricing" class="text-gray-400 hover:text-white transition-colors">الأسعار</a></li>
                        <li><a href="#faq" class="text-gray-400 hover:text-white transition-colors">الأسئلة الشائعة</a></li>
                        <li><a href="{{ route('login') }}" class="text-gray-400 hover:text-white transition-colors">تسجيل الدخول</a></li>
                    </ul>
                </div>
                
                <!-- Legal -->
                <div>
                    <h4 class="font-bold text-lg mb-6">قانوني</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">شروط الاستخدام</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">سياسة الخصوصية</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition-colors">سياسة الاسترجاع</a></li>
                    </ul>
                </div>
                
                <!-- Contact -->
                <div>
                    <h4 class="font-bold text-lg mb-6">تواصل معنا</h4>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <span>support@vpshopdz.com</span>
                        </li>
                        <li class="flex items-center gap-3 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                            <span dir="ltr">+213 XX XX XX XX</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-gray-500 text-sm">© {{ date('Y') }} VPShopDZ. جميع الحقوق محفوظة.</p>
                <p class="text-gray-500 text-sm flex items-center gap-2">
                    صنع بـ 
                    <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                    </svg>
                    في الجزائر 🇩🇿
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
