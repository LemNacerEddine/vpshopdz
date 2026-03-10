<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ═══════════════════════════════════════════════════════════════
        // 🏪 STORES (المتاجر) - Central Table
        // ═══════════════════════════════════════════════════════════════
        Schema::create('stores', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('owner_id')->nullable();
            
            // معلومات المتجر
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('name_ar')->nullable();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('cover_image')->nullable();
            
            // الاتصال
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('address')->nullable();
            
            // الإعدادات
            $table->string('currency', 10)->default('DZD');
            $table->string('language', 10)->default('ar');
            $table->string('timezone')->default('Africa/Algiers');
            $table->uuid('theme_id')->nullable();
            
            // الدومين
            $table->string('subdomain')->unique()->nullable();
            $table->string('custom_domain')->unique()->nullable();
            $table->boolean('ssl_enabled')->default(false);
            
            // Pixels & Analytics
            $table->string('facebook_pixel_id')->nullable();
            $table->string('tiktok_pixel_id')->nullable();
            $table->string('google_analytics_id')->nullable();
            $table->string('snapchat_pixel_id')->nullable();
            
            // Social Media
            $table->string('facebook_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('tiktok_url')->nullable();
            
            // الحالة
            $table->enum('status', ['active', 'inactive', 'suspended', 'trial'])->default('trial');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
            
            // الإحصائيات
            $table->unsignedInteger('products_count')->default(0);
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('total_revenue', 15, 2)->default(0);
            
            // JSON Settings
            $table->json('settings')->nullable();
            $table->json('shipping_settings')->nullable();
            $table->json('payment_settings')->nullable();
            $table->json('notification_settings')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'created_at']);
            $table->index('subdomain');
            $table->index('custom_domain');
        });

        // ═══════════════════════════════════════════════════════════════
        // 👤 USERS (المستخدمين) - Platform Level
        // ═══════════════════════════════════════════════════════════════
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone', 20)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            
            $table->string('avatar')->nullable();
            $table->enum('role', ['super_admin', 'store_owner', 'store_staff', 'customer'])->default('customer');
            
            // للتجار والموظفين
            $table->uuid('store_id')->nullable();
            $table->json('permissions')->nullable();
            
            // Google OAuth
            $table->string('google_id')->nullable()->unique();
            
            // Reset Password
            $table->string('reset_token')->nullable();
            $table->timestamp('reset_token_expires_at')->nullable();
            
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['role', 'store_id']);
            $table->index('email');
            $table->index('phone');
            
            $table->foreign('store_id')->references('id')->on('stores')->nullOnDelete();
        });

        // Sessions
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->uuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
            
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });

        // Password Reset Tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // ═══════════════════════════════════════════════════════════════
        // 💰 SUBSCRIPTION PLANS (باقات الاشتراك)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            
            $table->string('name');
            $table->string('name_ar');
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            
            // الأسعار
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_yearly', 10, 2)->nullable();
            $table->string('currency', 10)->default('DZD');
            
            // الحدود
            $table->unsignedInteger('max_products')->nullable();
            $table->unsignedInteger('max_orders_per_month')->nullable();
            $table->unsignedInteger('max_staff')->default(1);
            $table->unsignedInteger('max_categories')->nullable();
            $table->unsignedInteger('max_images_per_product')->default(5);
            
            // الميزات
            $table->boolean('custom_domain')->default(false);
            $table->boolean('remove_branding')->default(false);
            $table->boolean('advanced_analytics')->default(false);
            $table->boolean('api_access')->default(false);
            $table->boolean('priority_support')->default(false);
            $table->boolean('facebook_pixel')->default(true);
            $table->boolean('whatsapp_integration')->default(false);
            $table->boolean('abandoned_cart')->default(false);
            
            $table->json('features')->nullable();
            
            $table->unsignedInteger('trial_days')->default(7);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            
            $table->timestamps();
        });

        // ═══════════════════════════════════════════════════════════════
        // 📝 STORE SUBSCRIPTIONS (اشتراكات المتاجر)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('store_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('plan_id');
            
            $table->enum('status', ['active', 'cancelled', 'expired', 'past_due'])->default('active');
            $table->enum('billing_cycle', ['monthly', 'yearly'])->default('monthly');
            
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->timestamp('cancelled_at')->nullable();
            
            // الاستخدام
            $table->unsignedInteger('orders_this_month')->default(0);
            $table->unsignedInteger('products_count')->default(0);
            
            // الدفع
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('payment_reference')->nullable();
            $table->timestamp('last_payment_at')->nullable();
            
            $table->timestamps();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('subscription_plans');
            
            $table->index(['store_id', 'status']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 💳 PAYMENTS (سجل المدفوعات)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('subscription_id')->nullable();
            
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('DZD');
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->enum('method', ['ccp', 'baridimob', 'card', 'bank_transfer', 'cash'])->default('ccp');
            
            $table->string('reference')->nullable();
            $table->string('transaction_id')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('subscription_id')->references('id')->on('store_subscriptions')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
        Schema::dropIfExists('store_subscriptions');
        Schema::dropIfExists('subscription_plans');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('users');
        Schema::dropIfExists('stores');
    }
};
