<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ═══════════════════════════════════════════════════════════════
        // 👥 CUSTOMERS (زبائن المتجر) - Per Store
        // ═══════════════════════════════════════════════════════════════
        Schema::create('customers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 20);
            $table->string('phone2', 20)->nullable();
            
            $table->string('wilaya')->nullable();
            $table->string('commune')->nullable();
            $table->text('address')->nullable();
            
            $table->unsignedInteger('orders_count')->default(0);
            $table->decimal('total_spent', 15, 2)->default(0);
            $table->timestamp('last_order_at')->nullable();
            
            $table->text('notes')->nullable();
            $table->json('tags')->nullable();
            
            $table->timestamps();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            
            $table->unique(['store_id', 'phone']);
            $table->index(['store_id', 'created_at']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 📍 CUSTOMER ADDRESSES (عناوين الزبائن)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('customer_addresses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('customer_id');
            
            $table->string('label')->nullable();
            $table->string('full_name');
            $table->string('phone', 20);
            $table->string('wilaya');
            $table->string('commune')->nullable();
            $table->text('address_line');
            
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            $table->foreign('customer_id')->references('id')->on('customers')->cascadeOnDelete();
        });

        // ═══════════════════════════════════════════════════════════════
        // 🛒 ORDERS (الطلبات) - Per Store
        // ═══════════════════════════════════════════════════════════════
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('customer_id')->nullable();
            
            // رقم الطلب
            $table->string('order_number', 50);
            
            // معلومات الزبون
            $table->string('customer_name');
            $table->string('customer_phone', 20);
            $table->string('customer_phone2', 20)->nullable();
            $table->string('customer_email')->nullable();
            
            // العنوان
            $table->string('wilaya');
            $table->string('commune')->nullable();
            $table->text('shipping_address');
            
            // المبالغ
            $table->decimal('subtotal', 12, 2);
            $table->decimal('shipping_cost', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('total', 12, 2);
            
            // الحالة
            $table->enum('status', [
                'pending',      // في الانتظار
                'confirmed',    // مؤكد
                'processing',   // قيد التجهيز
                'shipped',      // تم الشحن
                'delivered',    // تم التوصيل
                'cancelled',    // ملغي
                'returned'      // مسترجع
            ])->default('pending');
            
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->string('payment_method', 50)->default('cod');
            
            // الشحن
            $table->uuid('shipping_company_id')->nullable();
            $table->string('tracking_number')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            
            // ملاحظات
            $table->text('customer_notes')->nullable();
            $table->text('internal_notes')->nullable();
            
            // المصدر
            $table->string('source')->default('website');
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            
            $table->unique(['store_id', 'order_number']);
            $table->index(['store_id', 'status', 'created_at']);
            $table->index(['store_id', 'customer_phone']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 📦 ORDER ITEMS (عناصر الطلب)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->uuid('product_id')->nullable();
            
            $table->string('product_name');
            $table->string('product_name_ar')->nullable();
            $table->string('product_image')->nullable();
            $table->string('product_sku')->nullable();
            
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            
            $table->json('variant_options')->nullable();
            
            $table->timestamps();
            
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->nullOnDelete();
        });

        // ═══════════════════════════════════════════════════════════════
        // 📜 ORDER STATUS HISTORY (تاريخ حالات الطلب)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('order_status_history', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('order_id');
            $table->uuid('changed_by')->nullable();
            
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->text('notes')->nullable();
            
            $table->timestamp('created_at');
            
            $table->foreign('order_id')->references('id')->on('orders')->cascadeOnDelete();
            $table->foreign('changed_by')->references('id')->on('users')->nullOnDelete();
        });

        // ═══════════════════════════════════════════════════════════════
        // 🛒 CARTS (السلات) - Per Store
        // ═══════════════════════════════════════════════════════════════
        Schema::create('carts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('customer_id')->nullable();
            $table->string('session_id')->nullable();
            
            $table->decimal('total', 12, 2)->default(0);
            $table->unsignedInteger('items_count')->default(0);
            
            $table->timestamps();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('customer_id')->references('id')->on('customers')->nullOnDelete();
            
            $table->index(['store_id', 'session_id']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 🛍️ CART ITEMS (عناصر السلة)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('cart_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('cart_id');
            $table->uuid('product_id');
            $table->uuid('variant_id')->nullable();
            
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            
            $table->timestamps();
            
            $table->foreign('cart_id')->references('id')->on('carts')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('variant_id')->references('id')->on('product_variants')->nullOnDelete();
            
            $table->unique(['cart_id', 'product_id', 'variant_id']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 🛒 ABANDONED CHECKOUTS (السلات المتروكة)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('abandoned_checkouts', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('cart_id')->nullable();
            
            $table->string('customer_name')->nullable();
            $table->string('customer_phone', 20)->nullable();
            $table->string('customer_email')->nullable();
            $table->string('wilaya')->nullable();
            $table->string('commune')->nullable();
            $table->text('shipping_address')->nullable();
            
            $table->json('items');
            $table->decimal('cart_total', 12, 2)->default(0);
            $table->unsignedInteger('items_count')->default(0);
            
            $table->boolean('recovered')->default(false);
            $table->uuid('recovered_order_id')->nullable();
            
            // WhatsApp Notification
            $table->enum('notification_status', ['pending', 'processing', 'sent', 'failed', 'skipped'])->default('pending');
            $table->timestamp('notified_at')->nullable();
            $table->unsignedTinyInteger('notification_attempts')->default(0);
            $table->text('last_error')->nullable();
            $table->string('whatsapp_message_id')->nullable();
            
            $table->timestamps();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            
            $table->index(['store_id', 'recovered', 'created_at']);
            $table->index(['store_id', 'notification_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abandoned_checkouts');
        Schema::dropIfExists('cart_items');
        Schema::dropIfExists('carts');
        Schema::dropIfExists('order_status_history');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('customer_addresses');
        Schema::dropIfExists('customers');
    }
};
