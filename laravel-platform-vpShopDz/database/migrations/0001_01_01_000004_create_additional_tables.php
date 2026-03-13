<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ═══════════════════════════════════════════════════════════════
        // 🚚 SHIPPING COMPANIES (شركات التوصيل)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('shipping_companies', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('name');
            $table->string('name_ar');
            $table->string('code', 50)->unique();
            $table->string('logo')->nullable();
            
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            
            // API Integration
            $table->string('api_url')->nullable();
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            
            $table->string('tracking_url_template')->nullable();
            
            // الأوزان
            $table->unsignedInteger('volumetric_divisor')->default(5000);
            $table->decimal('included_weight', 10, 2)->default(5.00);
            $table->decimal('price_per_extra_kg', 10, 2)->default(0);
            
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            
            $table->timestamps();
        });

        // ═══════════════════════════════════════════════════════════════
        // 🗺️ WILAYAS (الولايات الجزائرية)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('wilayas', function (Blueprint $table) {
            $table->unsignedTinyInteger('id')->primary();
            $table->string('name_ar', 100);
            $table->string('name_fr', 100)->nullable();
            $table->string('name_en', 100)->nullable();
        });

        // ═══════════════════════════════════════════════════════════════
        // 🏘️ COMMUNES (البلديات)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('communes', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('wilaya_id');
            $table->string('name_ar', 100);
            $table->string('name_fr', 100)->nullable();
            $table->string('postal_code', 10)->nullable();
            
            $table->foreign('wilaya_id')->references('id')->on('wilayas');
            $table->index('wilaya_id');
        });

        // ═══════════════════════════════════════════════════════════════
        // 💰 SHIPPING RATES (أسعار التوصيل)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('company_id');
            
            $table->unsignedTinyInteger('wilaya_id');
            $table->unsignedBigInteger('commune_id')->nullable();
            
            $table->enum('delivery_type', ['home', 'desk'])->default('home');
            $table->decimal('price', 10, 2);
            
            $table->unsignedTinyInteger('min_days')->default(1);
            $table->unsignedTinyInteger('max_days')->default(3);
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('company_id')->references('id')->on('shipping_companies')->cascadeOnDelete();
            $table->foreign('wilaya_id')->references('id')->on('wilayas');
            $table->foreign('commune_id')->references('id')->on('communes')->nullOnDelete();
            
            $table->unique(['company_id', 'wilaya_id', 'commune_id', 'delivery_type'], 'unique_shipping_rate');
        });

        // ═══════════════════════════════════════════════════════════════
        // 🏪 STORE SHIPPING SETTINGS (إعدادات الشحن للمتجر)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('store_shipping_settings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('company_id');
            
            $table->string('api_key')->nullable();
            $table->string('api_secret')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->json('custom_rates')->nullable();
            
            $table->timestamps();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('company_id')->references('id')->on('shipping_companies')->cascadeOnDelete();
            
            $table->unique(['store_id', 'company_id']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 🎁 SHIPPING RULES (قواعد الشحن المجاني)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('shipping_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            
            $table->string('name');
            $table->string('name_ar')->nullable();
            
            $table->enum('rule_type', [
                'min_cart_total',
                'min_cart_items', 
                'free_for_category',
                'free_for_product',
                'free_for_wilaya'
            ]);
            
            $table->string('condition_value');
            $table->decimal('shipping_cost_override', 10, 2)->default(0);
            
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->unsignedInteger('priority')->default(0);
            
            $table->timestamps();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });

        // ═══════════════════════════════════════════════════════════════
        // ⭐ REVIEWS (التقييمات)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('reviews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('product_id');
            $table->uuid('customer_id')->nullable();
            $table->uuid('order_id')->nullable();
            
            $table->string('customer_name');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            
            $table->boolean('is_approved')->default(true);
            $table->boolean('is_featured')->default(false);
            
            $table->timestamps();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            $table->foreign('order_id')->references('id')->on('orders')->nullOnDelete();
            
            $table->index(['product_id', 'is_approved']);
        });

        // ═══════════════════════════════════════════════════════════════
        // ❤️ WISHLISTS (قائمة الأمنيات)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('wishlists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('customer_id');
            $table->uuid('product_id');
            
            $table->timestamps();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            
            $table->unique(['customer_id', 'product_id']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 🎟️ COUPONS (الكوبونات)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            
            $table->string('code', 50);
            $table->string('name')->nullable();
            $table->text('description')->nullable();
            
            $table->enum('type', ['percentage', 'fixed_amount', 'free_shipping'])->default('percentage');
            $table->decimal('value', 10, 2);
            
            $table->decimal('minimum_order_amount', 10, 2)->nullable();
            $table->decimal('maximum_discount', 10, 2)->nullable();
            
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('usage_limit_per_customer')->default(1);
            $table->unsignedInteger('times_used')->default(0);
            
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            
            $table->json('applicable_products')->nullable();
            $table->json('applicable_categories')->nullable();
            
            $table->timestamps();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            
            $table->unique(['store_id', 'code']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 📊 STORE ANALYTICS (إحصائيات المتجر)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('store_analytics', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->date('date');
            
            $table->unsignedInteger('visitors')->default(0);
            $table->unsignedInteger('page_views')->default(0);
            $table->unsignedInteger('add_to_carts')->default(0);
            $table->unsignedInteger('checkouts_started')->default(0);
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            
            $table->timestamps();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            
            $table->unique(['store_id', 'date']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 🔔 NOTIFICATIONS (الإشعارات)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->uuidMorphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // ═══════════════════════════════════════════════════════════════
        // 📋 ACTIVITY LOG (سجل النشاط)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id')->nullable();
            $table->uuid('user_id')->nullable();
            
            $table->string('action');
            $table->string('subject_type')->nullable();
            $table->uuid('subject_id')->nullable();
            
            $table->json('properties')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            
            $table->timestamp('created_at');
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            
            $table->index(['store_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('store_analytics');
        Schema::dropIfExists('coupons');
        Schema::dropIfExists('wishlists');
        Schema::dropIfExists('reviews');
        Schema::dropIfExists('shipping_rules');
        Schema::dropIfExists('store_shipping_settings');
        Schema::dropIfExists('shipping_rates');
        Schema::dropIfExists('communes');
        Schema::dropIfExists('wilayas');
        Schema::dropIfExists('shipping_companies');
    }
};
