<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء متجر جديد - {{ config('app.name') }}</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Tajawal', sans-serif; }
        .gradient-bg {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 50%, #a7f3d0 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }
        .input-focus:focus {
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
        }
        .btn-hover {
            transition: all 0.3s ease;
        }
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .step-indicator {
            transition: all 0.3s ease;
        }
        .step-indicator.active {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            transform: scale(1.1);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen py-8 px-4">
    <!-- Background Decorations -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 right-20 w-72 h-72 bg-primary-300/30 rounded-full blur-3xl floating"></div>
        <div class="absolute bottom-20 left-20 w-96 h-96 bg-primary-200/40 rounded-full blur-3xl floating" style="animation-delay: 1s;"></div>
    </div>

    <div class="max-w-xl mx-auto relative z-10">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block" data-testid="logo-link">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-3xl mb-4 shadow-2xl shadow-primary-500/40 floating">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </a>
            <h1 class="text-3xl font-black text-gray-800 mb-2">إنشاء متجرك الإلكتروني</h1>
            <p class="text-gray-500 text-lg">ابدأ رحلتك في التجارة الإلكترونية اليوم</p>
        </div>
        
        <!-- Register Card -->
        <div class="glass-card rounded-3xl shadow-2xl p-8 border border-white/50">
            <!-- Free Trial Banner -->
            <div class="bg-gradient-to-l from-primary-500 to-primary-600 rounded-2xl p-5 mb-8 text-white">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-black text-xl">14 يوم تجربة مجانية</p>
                        <p class="text-white/80">بدون بطاقة ائتمانية • إلغاء في أي وقت</p>
                    </div>
                </div>
            </div>

            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl mb-6 flex items-start gap-3" data-testid="error-alert">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                @csrf
                
                <!-- Personal Info Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3 pb-3 border-b-2 border-primary-100">
                        <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-800">معلوماتك الشخصية</h3>
                    </div>
                    
                    <div>
                        <label for="name" class="block text-sm font-bold text-gray-700 mb-2">الاسم الكامل *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-primary-500 focus:bg-white input-focus transition-all outline-none text-gray-800"
                            placeholder="أحمد محمد"
                            data-testid="name-input">
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="email" class="block text-sm font-bold text-gray-700 mb-2">البريد الإلكتروني *</label>
                            <input type="email" name="email" id="email" value="{{ old('email') }}" required
                                class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-primary-500 focus:bg-white input-focus transition-all outline-none text-gray-800"
                                placeholder="example@email.com"
                                data-testid="email-input">
                        </div>
                        
                        <div>
                            <label for="phone" class="block text-sm font-bold text-gray-700 mb-2">رقم الهاتف</label>
                            <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                                class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-primary-500 focus:bg-white input-focus transition-all outline-none text-gray-800"
                                placeholder="0555 00 00 00"
                                data-testid="phone-input">
                        </div>
                    </div>
                </div>
                
                <!-- Store Info Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3 pb-3 border-b-2 border-primary-100">
                        <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-800">معلومات المتجر</h3>
                    </div>
                    
                    <div>
                        <label for="store_name" class="block text-sm font-bold text-gray-700 mb-2">اسم المتجر *</label>
                        <input type="text" name="store_name" id="store_name" value="{{ old('store_name') }}" required
                            class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-primary-500 focus:bg-white input-focus transition-all outline-none text-gray-800"
                            placeholder="متجر الأناقة"
                            data-testid="store-name-input">
                        <div class="mt-2 flex items-center gap-2 text-xs text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                            </svg>
                            <span>سيكون رابط متجرك: <code class="bg-gray-100 px-2 py-0.5 rounded">{{ config('app.url') }}/store/اسم-المتجر</code></span>
                        </div>
                    </div>
                </div>
                
                <!-- Password Section -->
                <div class="space-y-4">
                    <div class="flex items-center gap-3 pb-3 border-b-2 border-primary-100">
                        <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-800">كلمة المرور</h3>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="password" class="block text-sm font-bold text-gray-700 mb-2">كلمة المرور *</label>
                            <input type="password" name="password" id="password" required
                                class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-primary-500 focus:bg-white input-focus transition-all outline-none text-gray-800"
                                placeholder="8 أحرف على الأقل"
                                data-testid="password-input">
                        </div>
                        
                        <div>
                            <label for="password_confirmation" class="block text-sm font-bold text-gray-700 mb-2">تأكيد كلمة المرور *</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" required
                                class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-primary-500 focus:bg-white input-focus transition-all outline-none text-gray-800"
                                placeholder="أعد كتابة كلمة المرور"
                                data-testid="password-confirm-input">
                        </div>
                    </div>
                </div>
                
                <!-- Terms -->
                <div class="bg-gray-50 rounded-2xl p-4">
                    <label class="flex items-start gap-3 cursor-pointer group">
                        <input type="checkbox" name="terms" required 
                            class="w-5 h-5 text-primary-600 border-2 border-gray-300 rounded-lg focus:ring-primary-500 mt-0.5 cursor-pointer"
                            data-testid="terms-checkbox">
                        <span class="text-sm text-gray-600 group-hover:text-gray-800 transition-colors">
                            أوافق على 
                            <a href="#" class="text-primary-600 hover:underline font-medium">شروط الاستخدام</a> 
                            و 
                            <a href="#" class="text-primary-600 hover:underline font-medium">سياسة الخصوصية</a>
                        </span>
                    </label>
                </div>
                
                <button type="submit" 
                    class="w-full bg-gradient-to-l from-primary-600 to-primary-500 text-white py-4 rounded-2xl font-bold text-lg btn-hover shadow-lg shadow-primary-500/30 flex items-center justify-center gap-3"
                    data-testid="register-submit-btn">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    إنشاء متجري مجاناً
                </button>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-gray-500">
                    لديك حساب بالفعل؟ 
                    <a href="{{ route('login') }}" class="text-primary-600 font-bold hover:underline" data-testid="login-link">تسجيل الدخول</a>
                </p>
            </div>
        </div>
        
        <!-- Features Grid -->
        <div class="mt-8 grid grid-cols-3 gap-4">
            <div class="glass-card rounded-2xl p-4 text-center border border-white/50">
                <div class="w-12 h-12 bg-blue-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-700">آمن 100%</p>
                <p class="text-xs text-gray-500 mt-1">حماية SSL</p>
            </div>
            
            <div class="glass-card rounded-2xl p-4 text-center border border-white/50">
                <div class="w-12 h-12 bg-primary-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-700">سريع جداً</p>
                <p class="text-xs text-gray-500 mt-1">أداء عالي</p>
            </div>
            
            <div class="glass-card rounded-2xl p-4 text-center border border-white/50">
                <div class="w-12 h-12 bg-purple-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <p class="text-sm font-bold text-gray-700">دعم فني</p>
                <p class="text-xs text-gray-500 mt-1">24/7</p>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="mt-8 text-center">
            <p class="text-gray-500 text-sm flex items-center justify-center gap-2">
                صنع بـ 
                <svg class="w-4 h-4 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"></path>
                </svg>
                في الجزائر 🇩🇿
            </p>
        </div>
    </div>
</body>
</html>
