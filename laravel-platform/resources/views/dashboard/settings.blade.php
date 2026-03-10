@extends('layouts.dashboard')

@section('title', 'إعدادات المتجر')

@section('content')
<div class="max-w-4xl" x-data="{ activeTab: 'basic', saved: false }">
    <!-- Tabs -->
    <div class="bg-white rounded-2xl p-2 mb-6 flex flex-wrap gap-2 border border-gray-100">
        <button @click="activeTab = 'basic'" 
            :class="activeTab === 'basic' ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30' : 'text-gray-600 hover:bg-gray-100'"
            class="px-4 py-2.5 rounded-xl font-medium transition-all flex items-center gap-2"
            data-testid="tab-basic">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            الأساسية
        </button>
        <button @click="activeTab = 'contact'" 
            :class="activeTab === 'contact' ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30' : 'text-gray-600 hover:bg-gray-100'"
            class="px-4 py-2.5 rounded-xl font-medium transition-all flex items-center gap-2"
            data-testid="tab-contact">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
            </svg>
            التواصل
        </button>
        <button @click="activeTab = 'social'" 
            :class="activeTab === 'social' ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30' : 'text-gray-600 hover:bg-gray-100'"
            class="px-4 py-2.5 rounded-xl font-medium transition-all flex items-center gap-2"
            data-testid="tab-social">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
            </svg>
            التواصل الاجتماعي
        </button>
        <button @click="activeTab = 'branding'" 
            :class="activeTab === 'branding' ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30' : 'text-gray-600 hover:bg-gray-100'"
            class="px-4 py-2.5 rounded-xl font-medium transition-all flex items-center gap-2"
            data-testid="tab-branding">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
            </svg>
            الهوية البصرية
        </button>
        <button @click="activeTab = 'seo'" 
            :class="activeTab === 'seo' ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30' : 'text-gray-600 hover:bg-gray-100'"
            class="px-4 py-2.5 rounded-xl font-medium transition-all flex items-center gap-2"
            data-testid="tab-seo">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
            SEO
        </button>
        <button @click="activeTab = 'analytics'" 
            :class="activeTab === 'analytics' ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30' : 'text-gray-600 hover:bg-gray-100'"
            class="px-4 py-2.5 rounded-xl font-medium transition-all flex items-center gap-2"
            data-testid="tab-analytics">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            التحليلات
        </button>
    </div>

    <!-- Success Message -->
    <div x-show="saved" x-transition class="mb-6 p-4 bg-green-50 border border-green-200 rounded-2xl flex items-center gap-3" role="alert">
        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <p class="text-green-800 font-medium">تم حفظ التغييرات بنجاح!</p>
    </div>

    <form method="POST" action="{{ route('dashboard.settings.update') }}" class="space-y-6" @submit="saved = true; setTimeout(() => saved = false, 3000)">
        @csrf
        @method('PUT')
        
        <!-- Basic Info Tab -->
        <div x-show="activeTab === 'basic'" x-transition>
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-l from-primary-500 to-primary-600 px-6 py-4">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        المعلومات الأساسية
                    </h2>
                    <p class="text-white/70 text-sm">معلومات متجرك الأساسية التي تظهر للعملاء</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">اسم المتجر (عربي) *</label>
                            <input type="text" name="name_ar" value="{{ $store->name_ar }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                data-testid="store-name-ar">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">اسم المتجر (إنجليزي)</label>
                            <input type="text" name="name" value="{{ $store->name }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                data-testid="store-name-en">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">وصف المتجر</label>
                        <textarea name="description" rows="3"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"
                            placeholder="أضف وصفاً جذاباً لمتجرك..."
                            data-testid="store-description">{{ $store->description }}</textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">رابط المتجر</label>
                        <div class="flex items-center bg-gray-50 rounded-xl border border-gray-200 overflow-hidden">
                            <span class="px-4 py-3 bg-gray-100 text-gray-500 text-sm border-l border-gray-200">
                                {{ config('app.url') }}/store/
                            </span>
                            <input type="text" value="{{ $store->slug }}" disabled
                                class="flex-1 px-4 py-3 bg-transparent border-0 focus:ring-0 text-gray-500"
                                data-testid="store-slug">
                            <button type="button" class="px-4 text-primary-600 hover:text-primary-700 font-medium text-sm">
                                نسخ الرابط
                            </button>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">لا يمكن تغيير رابط المتجر بعد إنشائه</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Contact Info Tab -->
        <div x-show="activeTab === 'contact'" x-transition>
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-l from-blue-500 to-blue-600 px-6 py-4">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        معلومات التواصل
                    </h2>
                    <p class="text-white/70 text-sm">كيف يمكن للعملاء التواصل معك</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline-block ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                </svg>
                                رقم الهاتف
                            </label>
                            <input type="tel" name="phone" value="{{ $store->phone }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="0555 00 00 00"
                                data-testid="store-phone">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline-block ml-1 text-green-500" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                                </svg>
                                واتساب
                            </label>
                            <input type="tel" name="whatsapp" value="{{ $store->whatsapp }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="213555000000"
                                data-testid="store-whatsapp">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline-block ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                البريد الإلكتروني
                            </label>
                            <input type="email" name="email" value="{{ $store->email }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                data-testid="store-email">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                <svg class="w-4 h-4 inline-block ml-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                العنوان
                            </label>
                            <input type="text" name="address" value="{{ $store->address }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                data-testid="store-address">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Social Media Tab -->
        <div x-show="activeTab === 'social'" x-transition>
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-l from-purple-500 to-purple-600 px-6 py-4">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path>
                        </svg>
                        وسائل التواصل الاجتماعي
                    </h2>
                    <p class="text-white/70 text-sm">اربط متجرك بحساباتك على السوشيال ميديا</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                                فيسبوك
                            </label>
                            <input type="url" name="facebook_url" value="{{ $store->facebook_url }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="https://facebook.com/yourpage"
                                data-testid="store-facebook">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                <svg class="w-5 h-5 text-pink-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                                انستغرام
                            </label>
                            <input type="url" name="instagram_url" value="{{ $store->instagram_url }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="https://instagram.com/yourpage"
                                data-testid="store-instagram">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>
                                </svg>
                                تيك توك
                            </label>
                            <input type="url" name="tiktok_url" value="{{ $store->tiktok_url }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="https://tiktok.com/@yourpage"
                                data-testid="store-tiktok">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Branding Tab -->
        <div x-show="activeTab === 'branding'" x-transition>
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-l from-orange-500 to-orange-600 px-6 py-4">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"></path>
                        </svg>
                        الهوية البصرية
                    </h2>
                    <p class="text-white/70 text-sm">شعارك وألوان متجرك</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">شعار المتجر (URL)</label>
                            <input type="url" name="logo" value="{{ $store->logo }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                                placeholder="https://example.com/logo.png"
                                data-testid="store-logo">
                            @if($store->logo)
                                <div class="mt-3 p-4 bg-gray-50 rounded-xl">
                                    <img src="{{ $store->logo }}" alt="شعار المتجر" class="h-16 object-contain">
                                </div>
                            @endif
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">لون المتجر الأساسي</label>
                            <div class="flex items-center gap-4">
                                <input type="color" name="theme_color" value="{{ $store->theme_color ?? '#10b981' }}"
                                    class="w-20 h-14 border-2 border-gray-200 rounded-xl cursor-pointer"
                                    data-testid="store-color">
                                <div class="flex-1">
                                    <input type="text" value="{{ $store->theme_color ?? '#10b981' }}"
                                        class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all font-mono"
                                        readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- SEO Tab -->
        <div x-show="activeTab === 'seo'" x-transition>
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-l from-green-500 to-green-600 px-6 py-4">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        تحسين محركات البحث (SEO)
                    </h2>
                    <p class="text-white/70 text-sm">اجعل متجرك يظهر في نتائج Google</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">عنوان الصفحة (Title)</label>
                        <input type="text" name="seo_title" value="{{ $store->seo_title }}"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                            placeholder="اسم المتجر - أفضل المنتجات"
                            data-testid="store-seo-title">
                        <p class="text-xs text-gray-400 mt-1">يظهر في تبويب المتصفح ونتائج البحث (50-60 حرف)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">وصف الصفحة (Description)</label>
                        <textarea name="seo_description" rows="3"
                            class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all resize-none"
                            placeholder="وصف قصير يظهر في نتائج البحث..."
                            data-testid="store-seo-description">{{ $store->seo_description }}</textarea>
                        <p class="text-xs text-gray-400 mt-1">يظهر تحت العنوان في نتائج البحث (150-160 حرف)</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Analytics Tab -->
        <div x-show="activeTab === 'analytics'" x-transition>
            <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-l from-indigo-500 to-indigo-600 px-6 py-4">
                    <h2 class="text-lg font-bold text-white flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                        التتبع والتحليلات
                    </h2>
                    <p class="text-white/70 text-sm">تتبع زوارك وأداء إعلاناتك</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                                Facebook Pixel ID
                            </label>
                            <input type="text" name="facebook_pixel_id" value="{{ $store->facebook_pixel_id }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all font-mono"
                                placeholder="123456789012345"
                                data-testid="store-pixel">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                                <svg class="w-5 h-5 text-yellow-500" viewBox="0 0 24 24">
                                    <path fill="currentColor" d="M12.87 15.07l-2.54-2.51.03-.03c1.74-1.94 2.98-4.17 3.71-6.53H17V4h-7V2H8v2H1v1.99h11.17C11.5 7.92 10.44 9.75 9 11.35 8.07 10.32 7.3 9.19 6.69 8h-2c.73 1.63 1.73 3.17 2.98 4.56l-5.09 5.02L4 19l5-5 3.11 3.11.76-2.04zM18.5 10h-2L12 22h2l1.12-3h4.75L21 22h2l-4.5-12zm-2.62 7l1.62-4.33L19.12 17h-3.24z"/>
                                </svg>
                                Google Analytics ID
                            </label>
                            <input type="text" name="google_analytics_id" value="{{ $store->google_analytics_id }}"
                                class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all font-mono"
                                placeholder="G-XXXXXXXXXX"
                                data-testid="store-analytics">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Submit -->
        <div class="flex justify-end gap-3 pt-4">
            <button type="button" onclick="window.location.reload()" class="px-8 py-3 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition-all" data-testid="reset-btn">
                إلغاء التغييرات
            </button>
            <button type="submit" class="px-8 py-3 bg-gradient-to-l from-primary-600 to-primary-500 text-white rounded-xl font-bold hover:shadow-lg hover:shadow-primary-500/30 transition-all flex items-center gap-2" data-testid="save-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                حفظ التغييرات
            </button>
        </div>
    </form>
</div>
@endsection
