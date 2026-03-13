<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ═══════════════════════════════════════════════════════════════
        // 📁 CATEGORIES (الفئات) - Per Store
        // ═══════════════════════════════════════════════════════════════
        Schema::create('categories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_fr')->nullable();
            $table->string('slug');
            
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            
            $table->string('image')->nullable();
            $table->string('icon', 50)->default('Folder');
            
            $table->uuid('parent_id')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->unsignedInteger('products_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('categories')->nullOnDelete();
            
            $table->unique(['store_id', 'slug']);
            $table->index(['store_id', 'is_active', 'sort_order']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 📦 PRODUCTS (المنتجات) - Per Store
        // ═══════════════════════════════════════════════════════════════
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('store_id');
            $table->uuid('category_id')->nullable();
            
            // المعلومات الأساسية
            $table->string('name');
            $table->string('name_ar')->nullable();
            $table->string('name_fr')->nullable();
            $table->string('slug');
            
            $table->text('description')->nullable();
            $table->text('description_ar')->nullable();
            $table->text('description_fr')->nullable();
            
            // الأسعار
            $table->decimal('price', 12, 2);
            $table->decimal('compare_at_price', 12, 2)->nullable();
            $table->decimal('cost_price', 12, 2)->nullable();
            
            // الخصم
            $table->unsignedTinyInteger('discount_percent')->default(0);
            $table->timestamp('discount_starts_at')->nullable();
            $table->timestamp('discount_ends_at')->nullable();
            
            // المخزون
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->boolean('track_inventory')->default(true);
            $table->boolean('allow_backorder')->default(false);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            
            // الشحن
            $table->enum('shipping_type', ['standard', 'free', 'fixed_price'])->default('standard');
            $table->decimal('fixed_shipping_price', 10, 2)->nullable();
            $table->decimal('weight', 10, 2)->default(0);
            $table->decimal('length', 10, 2)->nullable();
            $table->decimal('width', 10, 2)->nullable();
            $table->decimal('height', 10, 2)->nullable();
            $table->boolean('is_fragile')->default(false);
            
            // الوحدة
            $table->string('unit', 20)->default('piece');
            
            // الحالة
            $table->enum('status', ['active', 'draft', 'archived'])->default('active');
            $table->boolean('is_featured')->default(false);
            
            // SEO
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            
            // الإحصائيات
            $table->decimal('rating', 2, 1)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('sold_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('store_id')->references('id')->on('stores')->cascadeOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            
            $table->unique(['store_id', 'slug']);
            $table->unique(['store_id', 'sku']);
            $table->index(['store_id', 'status', 'created_at']);
            $table->index(['store_id', 'category_id', 'status']);
            $table->index(['store_id', 'is_featured']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 🖼️ PRODUCT IMAGES (صور المنتجات)
        // ═══════════════════════════════════════════════════════════════
        Schema::create('product_images', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            
            $table->string('url');
            $table->string('alt_text')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_primary')->default(false);
            
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->index(['product_id', 'sort_order']);
        });

        // ═══════════════════════════════════════════════════════════════
        // 🏷️ PRODUCT VARIANTS (متغيرات المنتج) - للمستقبل
        // ═══════════════════════════════════════════════════════════════
        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('product_id');
            
            $table->string('name');
            $table->string('sku')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            
            $table->json('options')->nullable(); // {"size": "XL", "color": "أحمر"}
            $table->string('image')->nullable();
            
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('categories');
    }
};
