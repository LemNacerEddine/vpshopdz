<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Check if demo user already exists
        $existing = DB::table('users')->where('email', 'demo@vpshopdz.com')->first();
        if ($existing) {
            $this->command->info('ℹ️ Demo data already exists, skipping...');
            return;
        }

        // Create demo store owner
        $ownerId = Str::uuid()->toString();
        DB::table('users')->insert([
            'id' => $ownerId,
            'name' => 'أحمد التاجر',
            'email' => 'demo@vpshopdz.com',
            'password' => Hash::make('demo1234'),
            'phone' => '0555123456',
            'role' => 'store_owner',
            'email_verified_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create demo store
        $storeId = Str::uuid()->toString();
        DB::table('stores')->insert([
            'id' => $storeId,
            'owner_id' => $ownerId,
            'name' => 'متجر الأناقة',
            'slug' => 'elegance-store',
            'description' => 'متجر متخصص في الملابس والأزياء العصرية بأسعار مناسبة',
            'logo' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=200&h=200&fit=crop',
            'email' => 'contact@elegance-store.dz',
            'phone' => '0555123456',
            'whatsapp' => '213555123456',
            'address' => 'الجزائر العاصمة',
            'currency' => 'DZD',
            'language' => 'ar',
            'subdomain' => 'elegance-store',
            'status' => 'active',
            'facebook_url' => 'https://facebook.com/elegance-store',
            'instagram_url' => 'https://instagram.com/elegance-store',
            'products_count' => 15,
            'orders_count' => 22,
            'total_revenue' => 125000,
            'settings' => json_encode([
                'min_order' => 1000,
                'free_shipping_min' => 5000,
                'hero_slides' => [
                    [
                        'title' => 'أحدث صيحات الموضة',
                        'subtitle' => 'اكتشف مجموعتنا الجديدة من الملابس العصرية بأسعار لا تُقاوم',
                        'cta' => 'تسوق الآن',
                        'link' => '/products',
                        'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=1200&h=600&fit=crop',
                    ],
                    [
                        'title' => 'تشكيلة الصيف 2026',
                        'subtitle' => 'ملابس خفيفة وأنيقة لكل المناسبات',
                        'cta' => 'اكتشف المزيد',
                        'link' => '/products?sort=newest',
                        'image' => 'https://images.unsplash.com/photo-1469334031218-e382a71b716b?w=1200&h=600&fit=crop',
                    ],
                ],
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update user store_id
        DB::table('users')->where('id', $ownerId)->update(['store_id' => $storeId]);

        // Create categories with images
        $categories = [
            [
                'name' => 'ملابس رجالية',
                'slug' => 'men-clothes',
                'image' => 'https://images.unsplash.com/photo-1516257984-b1b4d707412e?w=400&h=400&fit=crop',
            ],
            [
                'name' => 'ملابس نسائية',
                'slug' => 'women-clothes',
                'image' => 'https://images.unsplash.com/photo-1483985988355-763728e1935b?w=400&h=400&fit=crop',
            ],
            [
                'name' => 'أحذية',
                'slug' => 'shoes',
                'image' => 'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=400&h=400&fit=crop',
            ],
            [
                'name' => 'إكسسوارات',
                'slug' => 'accessories',
                'image' => 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=400&h=400&fit=crop',
            ],
            [
                'name' => 'حقائب',
                'slug' => 'bags',
                'image' => 'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=400&h=400&fit=crop',
            ],
        ];

        $catIds = [];
        foreach ($categories as $i => $cat) {
            $catId = Str::uuid()->toString();
            $catIds[] = $catId;
            DB::table('categories')->insert([
                'id' => $catId,
                'store_id' => $storeId,
                'name' => $cat['name'],
                'slug' => $cat['slug'],
                'description' => 'قسم ' . $cat['name'],
                'image' => $cat['image'],
                'sort_order' => $i,
                'is_active' => true,
                'products_count' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create products with images
        $products = [
            [
                'name' => 'قميص كلاسيكي أبيض',
                'price' => 3500,
                'compare' => 4500,
                'cat' => 0,
                'stock' => 25,
                'images' => [
                    'https://images.unsplash.com/photo-1602810318383-e386cc2a3ccf?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1598033129183-c4f50c736f10?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'بنطلون جينز أزرق',
                'price' => 4200,
                'compare' => 5000,
                'cat' => 0,
                'stock' => 18,
                'images' => [
                    'https://images.unsplash.com/photo-1542272604-787c3835535d?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1555689502-c4b22d76c56f?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'جاكيت جلد أسود',
                'price' => 12000,
                'compare' => 15000,
                'cat' => 0,
                'stock' => 8,
                'images' => [
                    'https://images.unsplash.com/photo-1551028719-00167b16eac5?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1520975954732-35dd22299614?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'تيشيرت رياضي',
                'price' => 2200,
                'compare' => 2800,
                'cat' => 0,
                'stock' => 40,
                'images' => [
                    'https://images.unsplash.com/photo-1521572163474-6864f9cf17ab?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1503341504253-dff4815485f1?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'فستان سهرة أنيق',
                'price' => 8500,
                'compare' => 11000,
                'cat' => 1,
                'stock' => 12,
                'images' => [
                    'https://images.unsplash.com/photo-1566479179817-c0b5b4b4b4b4?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1515372039744-b8f02a3ae446?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'بلوزة حريرية',
                'price' => 3800,
                'compare' => 4500,
                'cat' => 1,
                'stock' => 22,
                'images' => [
                    'https://images.unsplash.com/photo-1564257631407-4deb1f99d992?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1485462537746-965f33f7f6a7?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'تنورة ميدي',
                'price' => 3200,
                'compare' => 0,
                'cat' => 1,
                'stock' => 15,
                'images' => [
                    'https://images.unsplash.com/photo-1583496661160-fb5886a0aaaa?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1577900232427-18219b9166a0?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'حذاء رياضي Nike',
                'price' => 9800,
                'compare' => 12000,
                'cat' => 2,
                'stock' => 30,
                'images' => [
                    'https://images.unsplash.com/photo-1542291026-7eec264c27ff?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1608231387042-66d1773070a5?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'حذاء كلاسيكي جلد',
                'price' => 7500,
                'compare' => 9000,
                'cat' => 2,
                'stock' => 10,
                'images' => [
                    'https://images.unsplash.com/photo-1449505278894-297fdb3edbc1?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1614252235316-8c857d38b5f4?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'صندل صيفي',
                'price' => 2800,
                'compare' => 0,
                'cat' => 2,
                'stock' => 35,
                'images' => [
                    'https://images.unsplash.com/photo-1603487742131-4160ec999306?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1560769629-975ec94e6a86?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'ساعة يد فاخرة',
                'price' => 15000,
                'compare' => 20000,
                'cat' => 3,
                'stock' => 5,
                'images' => [
                    'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1434056886845-dac89ffe9b56?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'نظارة شمسية Ray-Ban',
                'price' => 6500,
                'compare' => 8000,
                'cat' => 3,
                'stock' => 20,
                'images' => [
                    'https://images.unsplash.com/photo-1572635196237-14b3f281503f?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1511499767150-a48a237f0083?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'حقيبة ظهر عصرية',
                'price' => 4500,
                'compare' => 5500,
                'cat' => 4,
                'stock' => 15,
                'images' => [
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1622560480605-d83c853bc5c3?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'حقيبة يد نسائية',
                'price' => 5800,
                'compare' => 7000,
                'cat' => 4,
                'stock' => 10,
                'images' => [
                    'https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1584917865442-de89df76afd3?w=600&h=600&fit=crop',
                ],
            ],
            [
                'name' => 'محفظة جلدية',
                'price' => 2500,
                'compare' => 3000,
                'cat' => 4,
                'stock' => 45,
                'images' => [
                    'https://images.unsplash.com/photo-1627123424574-724758594e93?w=600&h=600&fit=crop',
                    'https://images.unsplash.com/photo-1606503153255-59d5e417b6f4?w=600&h=600&fit=crop',
                ],
            ],
        ];

        $productIds = [];
        foreach ($products as $i => $p) {
            $pid = Str::uuid()->toString();
            $productIds[] = $pid;
            DB::table('products')->insert([
                'id' => $pid,
                'store_id' => $storeId,
                'category_id' => $catIds[$p['cat']],
                'name' => $p['name'],
                'slug' => Str::slug($p['name']),
                'description' => 'منتج عالي الجودة - ' . $p['name'] . '. متوفر بعدة ألوان وأحجام.',
                'price' => $p['price'],
                'compare_at_price' => $p['compare'] > 0 ? $p['compare'] : null,
                'cost_price' => $p['price'] * 0.6,
                'sku' => 'ELG-' . str_pad($i + 1, 4, '0', STR_PAD_LEFT),
                'stock_quantity' => $p['stock'],
                'low_stock_threshold' => 5,
                'weight' => rand(200, 2000),
                'status' => 'active',
                'is_featured' => $i < 6 ? 1 : 0,
                'views_count' => rand(50, 500),
                'sold_count' => rand(5, 50),
                'created_at' => now()->subDays(rand(1, 60)),
                'updated_at' => now(),
            ]);

            // Insert product images
            foreach ($p['images'] as $imgIdx => $imgUrl) {
                DB::table('product_images')->insert([
                    'id' => Str::uuid()->toString(),
                    'product_id' => $pid,
                    'url' => $imgUrl,
                    'alt_text' => $p['name'],
                    'sort_order' => $imgIdx,
                    'is_primary' => $imgIdx === 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Create customers
        $customerData = [
            ['name' => 'محمد بن عمر', 'phone' => '0551234567', 'wilaya' => 'الجزائر', 'commune' => 'باب الوادي'],
            ['name' => 'فاطمة الزهراء', 'phone' => '0662345678', 'wilaya' => 'وهران', 'commune' => 'وهران المدينة'],
            ['name' => 'كريم بوزيد', 'phone' => '0773456789', 'wilaya' => 'قسنطينة', 'commune' => 'قسنطينة المدينة'],
            ['name' => 'سارة حداد', 'phone' => '0554567890', 'wilaya' => 'عنابة', 'commune' => 'عنابة المدينة'],
            ['name' => 'يوسف مراد', 'phone' => '0665678901', 'wilaya' => 'سطيف', 'commune' => 'سطيف المدينة'],
            ['name' => 'أمينة بلقاسم', 'phone' => '0776789012', 'wilaya' => 'بجاية', 'commune' => 'بجاية المدينة'],
            ['name' => 'عبد الرحمن', 'phone' => '0557890123', 'wilaya' => 'تلمسان', 'commune' => 'تلمسان المدينة'],
            ['name' => 'نورة سعيدي', 'phone' => '0668901234', 'wilaya' => 'البليدة', 'commune' => 'البليدة المدينة'],
        ];

        $customerIds = [];
        foreach ($customerData as $c) {
            $cid = Str::uuid()->toString();
            $customerIds[] = $cid;
            DB::table('customers')->insert([
                'id' => $cid,
                'store_id' => $storeId,
                'name' => $c['name'],
                'phone' => $c['phone'],
                'email' => Str::slug($c['name']) . '@gmail.com',
                'wilaya' => $c['wilaya'],
                'commune' => $c['commune'],
                'address' => 'حي ' . $c['commune'] . '، شارع رقم ' . rand(1, 50),
                'orders_count' => 0,
                'total_spent' => 0,
                'created_at' => now()->subDays(rand(1, 90)),
                'updated_at' => now(),
            ]);
        }

        // Create orders
        $statuses = ['pending', 'confirmed', 'shipped', 'delivered', 'cancelled'];
        $orderCount = 0;
        foreach ($statuses as $status) {
            $count = $status === 'delivered' ? 8 : ($status === 'pending' ? 5 : 3);
            for ($j = 0; $j < $count; $j++) {
                $orderCount++;
                $orderId = Str::uuid()->toString();
                $cIdx = array_rand($customerIds);
                $customerId = $customerIds[$cIdx];
                $customer = DB::table('customers')->where('id', $customerId)->first();
                $pIdx = array_rand($productIds);
                $productId = $productIds[$pIdx];
                $product = DB::table('products')->where('id', $productId)->first();
                $qty = rand(1, 3);
                $subtotal = $product->price * $qty;
                $shipping = rand(3, 8) * 100;
                $total = $subtotal + $shipping;

                DB::table('orders')->insert([
                    'id' => $orderId,
                    'store_id' => $storeId,
                    'customer_id' => $customerId,
                    'order_number' => 'ELG-' . str_pad($orderCount, 5, '0', STR_PAD_LEFT),
                    'status' => $status,
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shipping,
                    'discount_amount' => 0,
                    'total' => $total,
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'customer_email' => $customer->email,
                    'wilaya' => $customer->wilaya,
                    'commune' => $customer->commune,
                    'shipping_address' => $customer->address,
                    'payment_status' => $status === 'delivered' ? 'paid' : 'pending',
                    'payment_method' => 'cod',
                    'created_at' => now()->subDays(rand(0, 30)),
                    'updated_at' => now(),
                ]);

                // Order items
                DB::table('order_items')->insert([
                    'id' => Str::uuid()->toString(),
                    'order_id' => $orderId,
                    'product_id' => $productId,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_price' => $product->price,
                    'total_price' => $subtotal,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Create shipping companies (global - no store_id)
        $companies = [
            ['name' => 'Yalidine Delivery', 'name_ar' => 'يالدين ديليفري', 'code' => 'yalidine'],
            ['name' => 'ZR Express', 'name_ar' => 'زد ار اكسبرس', 'code' => 'zr-express'],
            ['name' => 'Ecotrack', 'name_ar' => 'ايكوتراك', 'code' => 'ecotrack'],
        ];

        foreach ($companies as $comp) {
            $exists = DB::table('shipping_companies')->where('code', $comp['code'])->exists();
            if (!$exists) {
                DB::table('shipping_companies')->insert([
                    'id' => Str::uuid()->toString(),
                    'name' => $comp['name'],
                    'name_ar' => $comp['name_ar'],
                    'code' => $comp['code'],
                    'is_active' => true,
                    'sort_order' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Create coupons
        DB::table('coupons')->insert([
            'id' => Str::uuid()->toString(),
            'store_id' => $storeId,
            'code' => 'WELCOME10',
            'name' => 'خصم ترحيبي',
            'type' => 'percentage',
            'value' => 10,
            'minimum_order_amount' => 3000,
            'usage_limit' => 100,
            'times_used' => 23,
            'is_active' => true,
            'starts_at' => now()->subMonth(),
            'ends_at' => now()->addMonths(3),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('coupons')->insert([
            'id' => Str::uuid()->toString(),
            'store_id' => $storeId,
            'code' => 'SUMMER500',
            'name' => 'خصم الصيف',
            'type' => 'fixed_amount',
            'value' => 500,
            'minimum_order_amount' => 5000,
            'usage_limit' => 50,
            'times_used' => 8,
            'is_active' => true,
            'starts_at' => now(),
            'ends_at' => now()->addMonths(2),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create reviews
        $reviewTexts = [
            'منتج ممتاز وجودة عالية، أنصح به بشدة!',
            'التوصيل كان سريع والمنتج مطابق للوصف',
            'جودة متوسطة مقارنة بالسعر',
            'رائع جداً، سأطلب مرة أخرى إن شاء الله',
            'المنتج جيد لكن التغليف كان ضعيف',
        ];

        for ($i = 0; $i < 5; $i++) {
            DB::table('reviews')->insert([
                'id' => Str::uuid()->toString(),
                'store_id' => $storeId,
                'product_id' => $productIds[$i],
                'customer_id' => $customerIds[$i],
                'customer_name' => $customerData[$i]['name'],
                'rating' => rand(3, 5),
                'comment' => $reviewTexts[$i],
                'is_approved' => $i < 3 ? 1 : 0,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('✅ Demo data created successfully!');
        $this->command->info('   Login: demo@vpshopdz.com / demo1234');
        $this->command->info('   Store: متجر الأناقة (elegance-store)');
        $this->command->info('   Products: 15 (with images) | Orders: 22 | Customers: 8');
    }
}
