<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPShopDZ - منصة التجارة الإلكترونية</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Tajawal', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-emerald-50 to-teal-100 min-h-screen">
    <div class="container mx-auto px-4 py-16">
        <!-- Header -->
        <div class="text-center mb-16">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-emerald-600 rounded-2xl mb-6 shadow-lg">
                <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                </svg>
            </div>
            <h1 class="text-4xl md:text-5xl font-extrabold text-gray-800 mb-4">
                VPShopDZ
            </h1>
            <p class="text-xl text-gray-600 max-w-2xl mx-auto">
                منصة التجارة الإلكترونية المتكاملة للسوق الجزائري
            </p>
            <div class="mt-4 inline-flex items-center px-4 py-2 bg-emerald-100 text-emerald-700 rounded-full text-sm font-medium">
                <span class="w-2 h-2 bg-emerald-500 rounded-full mr-2 animate-pulse"></span>
                الخادم يعمل بنجاح ✓
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-16">
            <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                <div class="text-3xl font-bold text-emerald-600">58</div>
                <div class="text-gray-500 text-sm">ولاية</div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                <div class="text-3xl font-bold text-emerald-600">1011</div>
                <div class="text-gray-500 text-sm">بلدية</div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                <div class="text-3xl font-bold text-emerald-600">60+</div>
                <div class="text-gray-500 text-sm">شركة توصيل</div>
            </div>
            <div class="bg-white rounded-2xl p-6 shadow-sm text-center">
                <div class="text-3xl font-bold text-emerald-600">5</div>
                <div class="text-gray-500 text-sm">باقات اشتراك</div>
            </div>
        </div>

        <!-- API Endpoints -->
        <div class="bg-white rounded-3xl shadow-lg p-8 mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-6 h-6 ml-2 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                </svg>
                نقاط API المتاحة
            </h2>
            
            <div class="space-y-4">
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                    <div>
                        <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded mr-2">GET</span>
                        <code class="text-gray-700">/api/v1/plans</code>
                    </div>
                    <span class="text-gray-500 text-sm">باقات الاشتراك</span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                    <div>
                        <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded mr-2">GET</span>
                        <code class="text-gray-700">/api/v1/store/{slug}/wilayas</code>
                    </div>
                    <span class="text-gray-500 text-sm">قائمة الولايات</span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                    <div>
                        <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded mr-2">GET</span>
                        <code class="text-gray-700">/api/v1/store/{slug}/communes/{wilayaId}</code>
                    </div>
                    <span class="text-gray-500 text-sm">بلديات الولاية</span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                    <div>
                        <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded mr-2">POST</span>
                        <code class="text-gray-700">/api/v1/auth/register</code>
                    </div>
                    <span class="text-gray-500 text-sm">تسجيل تاجر جديد</span>
                </div>
                
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                    <div>
                        <span class="inline-block px-2 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded mr-2">POST</span>
                        <code class="text-gray-700">/api/v1/auth/login</code>
                    </div>
                    <span class="text-gray-500 text-sm">تسجيل الدخول</span>
                </div>
            </div>
        </div>

        <!-- Quick Test -->
        <div class="bg-emerald-600 rounded-3xl shadow-lg p-8 text-white">
            <h2 class="text-2xl font-bold mb-4">🧪 اختبار سريع</h2>
            <p class="mb-4 opacity-90">جرب هذه الروابط للتأكد من عمل الـ API:</p>
            <div class="space-y-2 font-mono text-sm bg-emerald-700 rounded-xl p-4">
                <p>curl http://127.0.0.1:8000/health</p>
                <p>curl http://127.0.0.1:8000/api/v1/plans</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-16 text-gray-500">
            <p>صنع بـ ❤️ في الجزائر 🇩🇿</p>
            <p class="text-sm mt-2">Laravel 11 + MySQL</p>
        </div>
    </div>
</body>
</html>
