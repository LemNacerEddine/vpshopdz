<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ═══════════════════════════════════════════════════════════════
        // 🎨 THEMES (الثيمات)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('themes', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('name');
            $table->string('name_ar');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();

            $table->string('thumbnail')->nullable();
            $table->string('preview_url')->nullable();

            // التصنيف
            $table->enum('category', [
                'general',
                'fashion',
                'electronics',
                'food',
                'beauty',
                'sports',
                'home',
                'minimal',
                'luxury',
                'services',
                'gaming',
                'sahara'
            ])->default('general');

            // الإعدادات الافتراضية
            $table->json('default_colors')->nullable();
            $table->json('default_fonts')->nullable();
            $table->json('default_layout')->nullable();
            $table->json('sections')->nullable();
            $table->json('settings_schema')->nullable();

            $table->boolean('is_free')->default(true);
            $table->decimal('price', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('installs_count')->default(0);
            $table->unsignedInteger('sort_order')->default(0);

            $table->string('version', 20)->default('1.0.0');
            $table->string('author')->nullable();

            $table->timestamps();
        });

        // ═══════════════════════════════════════════════════════════════
        // 🎨 STORE THEMES (ثيمات المتاجر - التخصيص)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('store_themes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('theme_id');

            $table->boolean('is_active')->default(true);

            // تخصيصات التاجر
            $table->json('custom_colors')->nullable();
            $table->json('custom_fonts')->nullable();
            $table->json('custom_layout')->nullable();
            $table->json('custom_sections')->nullable();
            $table->json('custom_css')->nullable();
            $table->json('header_settings')->nullable();
            $table->json('footer_settings')->nullable();
            $table->json('homepage_settings')->nullable();

            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('theme_id')->references('id')->on('themes')->cascadeOnDelete();

            $table->unique(['store_id', 'theme_id']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 📢 FACEBOOK ADS (إعلانات فيسبوك)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('facebook_ads', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('product_id')->nullable();

            $table->string('campaign_id')->nullable();
            $table->string('campaign_name')->nullable();
            $table->string('adset_id')->nullable();
            $table->string('creative_id')->nullable();
            $table->string('fb_ad_id')->nullable();

            $table->enum('status', ['draft', 'pending', 'active', 'paused', 'completed', 'error'])->default('draft');
            $table->text('error_message')->nullable();

            $table->integer('daily_budget_cents')->default(0);
            $table->unsignedSmallInteger('duration_days')->default(7);

            // Targeting
            $table->string('target_country', 10)->default('DZ');
            $table->unsignedTinyInteger('target_age_min')->default(18);
            $table->unsignedTinyInteger('target_age_max')->default(65);
            $table->json('target_interests')->nullable();

            // Creative
            $table->text('ad_text')->nullable();
            $table->string('ad_headline')->nullable();
            $table->string('landing_url')->nullable();
            $table->string('image_hash')->nullable();

            // Metrics
            $table->unsignedInteger('impressions')->default(0);
            $table->unsignedInteger('clicks')->default(0);
            $table->integer('spend_cents')->default(0);
            $table->unsignedInteger('reach')->default(0);
            $table->timestamp('metrics_updated_at')->nullable();

            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();

            $table->index(['store_id', 'status']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 📊 STORE PIXELS (بكسلات التتبع)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('store_pixels', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');

            $table->enum('platform', [
                'facebook',
                'tiktok',
                'snapchat',
                'google_analytics',
                'google_ads',
                'twitter',
                'pinterest',
                'custom'
            ]);

            $table->string('pixel_id');
            $table->string('name')->nullable();
            $table->string('access_token')->nullable();
            $table->boolean('is_active')->default(true);

            // Events to track
            $table->json('tracked_events')->nullable();
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->index(['store_id', 'platform', 'is_active']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 🔗 STORE PAGES (صفحات المتجر المخصصة)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('store_pages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');

            $table->string('title');
            $table->string('title_ar')->nullable();
            $table->string('slug');
            $table->longText('content')->nullable();
            $table->longText('content_ar')->nullable();

            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->unique(['store_id', 'slug']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 📧 NOTIFICATION TEMPLATES (قوالب الإشعارات)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');

            $table->enum('channel', ['whatsapp', 'telegram', 'sms', 'email']);
            $table->enum('event', [
                'order_created',
                'order_confirmed',
                'order_shipped',
                'order_delivered',
                'order_cancelled',
                'abandoned_cart',
                'welcome_customer',
                'custom'
            ]);

            $table->string('name');
            $table->text('template_ar')->nullable();
            $table->text('template_fr')->nullable();
            $table->text('template_en')->nullable();

            $table->boolean('is_active')->default(true);
            $table->json('settings')->nullable();

            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->unique(['store_id', 'channel', 'event']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 📱 STORE INTEGRATIONS (تكاملات المتجر)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('store_integrations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');

            $table->enum('type', [
                'whatsapp_green_api',
                'whatsapp_business',
                'telegram_bot',
                'facebook_marketing',
                'google_merchant',
                'yalidine_api',
                'zr_express_api',
                'echrily_api',
                'maystro_api',
                'custom_webhook'
            ]);

            $table->string('name')->nullable();
            $table->json('credentials')->nullable();
            $table->json('settings')->nullable();

            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->text('last_error')->nullable();

            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->unique(['store_id', 'type']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 🏷️ STORE DOMAINS (نطاقات المتاجر)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('store_domains', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');

            $table->string('domain')->unique();
            $table->enum('type', ['subdomain', 'custom'])->default('subdomain');
            $table->boolean('is_primary')->default(false);
            $table->boolean('is_verified')->default(false);

            $table->enum('ssl_status', ['pending', 'active', 'failed', 'expired'])->default('pending');
            $table->timestamp('ssl_expires_at')->nullable();

            $table->enum('dns_status', ['pending', 'verified', 'failed'])->default('pending');
            $table->timestamp('dns_verified_at')->nullable();

            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });

        // ═══════════════════════════════════════════════════════════════
        // 📦 ORDER SEQUENCE (تسلسل أرقام الطلبات)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('order_sequences', function (Blueprint $table) {
            $table->uuid('store_id');
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('last_number')->default(0);

            $table->primary(['store_id', 'year']);
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
        });

        // ═══════════════════════════════════════════════════════════════
        // 👁️ BROWSING HISTORY (سجل التصفح)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('browsing_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('customer_id')->nullable();
            $table->string('session_id')->nullable();
            $table->uuid('product_id');

            $table->timestamp('viewed_at');

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();

            $table->index(['store_id', 'session_id', 'viewed_at']);
            $table->index(['store_id', 'customer_id', 'viewed_at']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 📋 STORE STAFF INVITATIONS (دعوات الموظفين)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('staff_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('invited_by');

            $table->string('email');
            $table->string('name')->nullable();
            $table->json('permissions')->nullable();
            $table->string('token')->unique();

            $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();

            $table->timestamps();

            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('invited_by')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_invitations');
        Schema::dropIfExists('browsing_history');
        Schema::dropIfExists('order_sequences');
        Schema::dropIfExists('store_domains');
        Schema::dropIfExists('store_integrations');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('store_pages');
        Schema::dropIfExists('store_pixels');
        Schema::dropIfExists('facebook_ads');
        Schema::dropIfExists('store_themes');
        Schema::dropIfExists('themes');
    }
};
