<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-emerald-50 to-teal-100 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Logo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-600 rounded-2xl mb-4 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-800">{{ config('app.name') }}</h1>
            <p class="text-gray-500">منصة التجارة الإلكترونية</p>
        </div>
        
        <!-- Login Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <h2 class="text-xl font-bold text-gray-800 mb-6 text-center">تسجيل الدخول</h2>
            
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                        placeholder="example@email.com">
                </div>
                
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور</label>
                    <input type="password" name="password" id="password" required
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                        placeholder="••••••••">
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                        <span class="mr-2 text-sm text-gray-600">تذكرني</span>
                    </label>
                    <a href="#" class="text-sm text-emerald-600 hover:underline">نسيت كلمة المرور؟</a>
                </div>
                
                <button type="submit" class="w-full bg-emerald-600 text-white py-3 rounded-xl font-bold hover:bg-emerald-700 transition">
                    تسجيل الدخول
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-500">ليس لديك حساب؟ <a href="{{ route('register') }}" class="text-emerald-600 font-bold hover:underline">إنشاء متجر جديد</a></p>
            </div>
        </div>
        
        <p class="text-center text-gray-400 text-sm mt-8">صنع بـ ❤️ في الجزائر 🇩🇿</p>
    </div>
</body>
</html>
