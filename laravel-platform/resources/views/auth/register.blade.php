<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء متجر جديد - {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>body { font-family: 'Tajawal', sans-serif; }</style>
</head>
<body class="bg-gradient-to-br from-emerald-50 to-teal-100 min-h-screen py-8 px-4">
    <div class="max-w-lg mx-auto">
        <!-- Logo -->
        <div class="text-center mb-8">
            <a href="{{ route('home') }}" class="inline-flex items-center justify-center w-16 h-16 bg-emerald-600 rounded-2xl mb-4 shadow-lg">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-800">إنشاء متجرك الإلكتروني</h1>
            <p class="text-gray-500">ابدأ رحلتك في التجارة الإلكترونية مجاناً</p>
        </div>
        
        <!-- Register Card -->
        <div class="bg-white rounded-3xl shadow-xl p-8">
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                    @foreach($errors->all() as $error)
                        <p>{{ $error }}</p>
                    @endforeach
                </div>
            @endif
            
            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf
                
                <div class="bg-emerald-50 rounded-xl p-4 mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="font-bold text-emerald-800">7 أيام تجربة مجانية</p>
                            <p class="text-sm text-emerald-600">بدون بطاقة ائتمانية</p>
                        </div>
                    </div>
                </div>
                
                <!-- Personal Info -->
                <div class="space-y-4">
                    <h3 class="font-bold text-gray-700 border-b pb-2">معلوماتك الشخصية</h3>
                    
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">الاسم الكامل *</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                            placeholder="أحمد محمد">
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني *</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                            placeholder="example@email.com">
                    </div>
                    
                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف</label>
                        <input type="tel" name="phone" id="phone" value="{{ old('phone') }}"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                            placeholder="0555 00 00 00">
                    </div>
                </div>
                
                <!-- Store Info -->
                <div class="space-y-4 pt-4">
                    <h3 class="font-bold text-gray-700 border-b pb-2">معلومات المتجر</h3>
                    
                    <div>
                        <label for="store_name" class="block text-sm font-medium text-gray-700 mb-2">اسم المتجر *</label>
                        <input type="text" name="store_name" id="store_name" value="{{ old('store_name') }}" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                            placeholder="متجر الأناقة">
                        <p class="text-xs text-gray-400 mt-1">سيكون رابط متجرك: vpshopdz.com/store/اسم-المتجر</p>
                    </div>
                </div>
                
                <!-- Password -->
                <div class="space-y-4 pt-4">
                    <h3 class="font-bold text-gray-700 border-b pb-2">كلمة المرور</h3>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">كلمة المرور *</label>
                        <input type="password" name="password" id="password" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                            placeholder="8 أحرف على الأقل">
                    </div>
                    
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">تأكيد كلمة المرور *</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                            placeholder="أعد كتابة كلمة المرور">
                    </div>
                </div>
                
                <div class="pt-4">
                    <label class="flex items-start gap-3">
                        <input type="checkbox" name="terms" required class="w-5 h-5 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500 mt-0.5">
                        <span class="text-sm text-gray-600">أوافق على <a href="#" class="text-emerald-600 hover:underline">شروط الاستخدام</a> و <a href="#" class="text-emerald-600 hover:underline">سياسة الخصوصية</a></span>
                    </label>
                </div>
                
                <button type="submit" class="w-full bg-emerald-600 text-white py-4 rounded-xl font-bold hover:bg-emerald-700 transition text-lg">
                    إنشاء متجري مجاناً
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-500">لديك حساب بالفعل؟ <a href="{{ route('login') }}" class="text-emerald-600 font-bold hover:underline">تسجيل الدخول</a></p>
            </div>
        </div>
        
        <!-- Features -->
        <div class="mt-8 grid grid-cols-3 gap-4 text-center">
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <p class="text-sm text-gray-600">آمن 100%</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <p class="text-sm text-gray-600">سريع</p>
            </div>
            <div class="bg-white rounded-2xl p-4 shadow-sm">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
                    <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"></path>
                    </svg>
                </div>
                <p class="text-sm text-gray-600">دعم فني</p>
            </div>
        </div>
        
        <p class="text-center text-gray-400 text-sm mt-8">صنع بـ ❤️ في الجزائر 🇩🇿</p>
    </div>
</body>
</html>
