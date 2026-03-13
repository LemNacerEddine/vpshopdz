<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - {{ config('app.name') }}</title>
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
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <!-- Background Decorations -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 right-20 w-72 h-72 bg-primary-300/30 rounded-full blur-3xl floating"></div>
        <div class="absolute bottom-20 left-20 w-96 h-96 bg-primary-200/40 rounded-full blur-3xl floating" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/3 w-64 h-64 bg-primary-400/20 rounded-full blur-3xl floating" style="animation-delay: 2s;"></div>
    </div>

    <div class="w-full max-w-md relative z-10">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-block" data-testid="logo-link">
                <div class="inline-flex items-center justify-center w-20 h-20 bg-gradient-to-br from-primary-500 to-primary-600 rounded-3xl mb-4 shadow-2xl shadow-primary-500/40 floating">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                    </svg>
                </div>
            </a>
            <h1 class="text-3xl font-black text-gray-800 mb-2">{{ config('app.name', 'VPShopDZ') }}</h1>
            <p class="text-gray-500 text-lg">منصة التجارة الإلكترونية الجزائرية</p>
        </div>
        
        <!-- Login Card -->
        <div class="glass-card rounded-3xl shadow-2xl p-8 border border-white/50">
            <div class="flex items-center justify-center gap-3 mb-8">
                <div class="h-px flex-1 bg-gradient-to-l from-primary-300 to-transparent"></div>
                <h2 class="text-xl font-bold text-gray-800">تسجيل الدخول</h2>
                <div class="h-px flex-1 bg-gradient-to-r from-primary-300 to-transparent"></div>
            </div>
            
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-2xl mb-6 flex items-center gap-3" data-testid="error-alert">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        @foreach($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-bold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        البريد الإلكتروني
                    </label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-primary-500 focus:bg-white input-focus transition-all outline-none text-gray-800"
                        placeholder="example@email.com"
                        data-testid="email-input">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-bold text-gray-700 mb-2">
                        <svg class="w-4 h-4 inline-block ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        كلمة المرور
                    </label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-5 py-4 bg-gray-50 border-2 border-gray-100 rounded-2xl focus:border-primary-500 focus:bg-white input-focus transition-all outline-none text-gray-800"
                        placeholder="••••••••"
                        data-testid="password-input">
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer group">
                        <input type="checkbox" name="remember" class="w-5 h-5 text-primary-600 border-2 border-gray-300 rounded-lg focus:ring-primary-500 focus:ring-offset-0 cursor-pointer" data-testid="remember-checkbox">
                        <span class="mr-3 text-sm text-gray-600 group-hover:text-gray-800 transition-colors">تذكرني</span>
                    </label>
                    <a href="#" class="text-sm text-primary-600 hover:text-primary-700 font-medium hover:underline" data-testid="forgot-password-link">نسيت كلمة المرور؟</a>
                </div>
                
                <button type="submit" 
                    class="w-full bg-gradient-to-l from-primary-600 to-primary-500 text-white py-4 rounded-2xl font-bold text-lg btn-hover shadow-lg shadow-primary-500/30"
                    data-testid="login-submit-btn">
                    <svg class="w-5 h-5 inline-block ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                    </svg>
                    تسجيل الدخول
                </button>
            </form>
            
            <div class="mt-8 pt-6 border-t border-gray-100 text-center">
                <p class="text-gray-500">
                    ليس لديك حساب؟ 
                    <a href="{{ route('register') }}" class="text-primary-600 font-bold hover:underline" data-testid="register-link">
                        إنشاء متجر جديد
                        <svg class="w-4 h-4 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                        </svg>
                    </a>
                </p>
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
            <p class="text-xs text-gray-400 mt-2">© {{ date('Y') }} {{ config('app.name') }}. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</body>
</html>
