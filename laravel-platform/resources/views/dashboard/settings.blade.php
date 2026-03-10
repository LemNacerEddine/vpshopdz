@extends('layouts.dashboard')

@section('title', 'إعدادات المتجر')

@section('content')
<div class="max-w-4xl">
    <form method="POST" action="#" class="space-y-6">
        @csrf
        @method('PUT')
        
        <!-- Basic Info -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-6 border-b pb-4">المعلومات الأساسية</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسم المتجر (عربي)</label>
                    <input type="text" name="name_ar" value="{{ $store->name_ar }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسم المتجر (إنجليزي)</label>
                    <input type="text" name="name" value="{{ $store->name }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                </div>
                
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">وصف المتجر</label>
                    <textarea name="description" rows="3"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500">{{ $store->description }}</textarea>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">رابط المتجر</label>
                    <div class="flex items-center">
                        <span class="bg-gray-100 px-4 py-3 border border-l-0 border-gray-200 rounded-r-xl text-gray-500 text-sm">
                            /store/
                        </span>
                        <input type="text" value="{{ $store->slug }}" disabled
                            class="flex-1 px-4 py-3 border border-gray-200 rounded-l-xl bg-gray-50 text-gray-500">
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Info -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-6 border-b pb-4">معلومات التواصل</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">رقم الهاتف</label>
                    <input type="tel" name="phone" value="{{ $store->phone }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="0555 00 00 00">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">واتساب</label>
                    <input type="tel" name="whatsapp" value="{{ $store->whatsapp }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="213555000000">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ $store->email }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">العنوان</label>
                    <input type="text" name="address" value="{{ $store->address }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500">
                </div>
            </div>
        </div>
        
        <!-- Social Media -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-6 border-b pb-4">وسائل التواصل الاجتماعي</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">فيسبوك</label>
                    <input type="url" name="facebook_url" value="{{ $store->facebook_url }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="https://facebook.com/...">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">انستغرام</label>
                    <input type="url" name="instagram_url" value="{{ $store->instagram_url }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="https://instagram.com/...">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تيك توك</label>
                    <input type="url" name="tiktok_url" value="{{ $store->tiktok_url }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="https://tiktok.com/...">
                </div>
            </div>
        </div>
        
        <!-- Branding -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-6 border-b pb-4">الهوية البصرية</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">شعار المتجر (URL)</label>
                    <input type="url" name="logo" value="{{ $store->logo }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="https://...">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">لون المتجر الأساسي</label>
                    <input type="color" name="theme_color" value="{{ $store->theme_color ?? '#10b981' }}"
                        class="w-full h-12 border border-gray-200 rounded-xl cursor-pointer">
                </div>
            </div>
        </div>
        
        <!-- SEO -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-6 border-b pb-4">تحسين محركات البحث (SEO)</h2>
            
            <div class="space-y-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">عنوان الصفحة (Title)</label>
                    <input type="text" name="seo_title" value="{{ $store->seo_title }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="اسم المتجر - أفضل المنتجات">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">وصف الصفحة (Description)</label>
                    <textarea name="seo_description" rows="2"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="وصف قصير يظهر في نتائج البحث...">{{ $store->seo_description }}</textarea>
                </div>
            </div>
        </div>
        
        <!-- Analytics -->
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h2 class="text-lg font-bold text-gray-800 mb-6 border-b pb-4">التتبع والتحليلات</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Facebook Pixel ID</label>
                    <input type="text" name="facebook_pixel_id" value="{{ $store->facebook_pixel_id }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="123456789012345">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Google Analytics ID</label>
                    <input type="text" name="google_analytics_id" value="{{ $store->google_analytics_id }}"
                        class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500"
                        placeholder="G-XXXXXXXXXX">
                </div>
            </div>
        </div>
        
        <!-- Submit -->
        <div class="flex justify-end">
            <button type="submit" class="bg-emerald-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-emerald-700 transition">
                حفظ التغييرات
            </button>
        </div>
    </form>
</div>
@endsection
