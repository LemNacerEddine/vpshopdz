<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ShippingController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\PageController;

/*
|--------------------------------------------------------------------------
| Storefront API Routes
|--------------------------------------------------------------------------
|
| These routes are for the public storefront React app.
| All routes are prefixed with /api/storefront/{storeSlug}
| No authentication required for most endpoints.
|
*/

Route::prefix('api/storefront/{storeSlug}')->group(function () {

    // Store Info
    Route::get('/', function ($storeSlug) {
        $store = \App\Models\Store::where('slug', $storeSlug)
            ->where('is_active', true)
            ->with(['activeTheme.theme', 'categories'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
                'logo' => $store->logo,
                'description' => $store->description,
                'currency' => $store->currency ?? 'DZD',
                'default_language' => $store->default_language ?? 'ar',
                'theme' => [
                    'slug' => $store->activeTheme?->theme?->slug ?? 'dawn',
                    'colors' => $store->activeTheme?->custom_colors ?? [],
                    'fonts' => $store->activeTheme?->custom_fonts ?? [],
                    'layout' => $store->activeTheme?->custom_layout ?? [],
                ],
                'social_links' => [
                    'facebook' => $store->facebook_url,
                    'instagram' => $store->instagram_url,
                    'whatsapp' => $store->whatsapp_number,
                    'tiktok' => $store->tiktok_url,
                ],
                'categories' => $store->categories->map(fn($c) => [
                    'id' => $c->id,
                    'name_ar' => $c->name_ar,
                    'name_fr' => $c->name_fr,
                    'name_en' => $c->name_en,
                    'slug' => $c->slug,
                    'image' => $c->image,
                ]),
            ],
        ]);
    });

    // Products
    Route::get('/products', function ($storeSlug) {
        $store = \App\Models\Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();

        $query = \App\Models\Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->with(['images', 'category', 'variants']);

        // Search
        if (request('search')) {
            $search = request('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_fr', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('description_ar', 'like', "%{$search}%");
            });
        }

        // Category filter
        if (request('category_id')) {
            $query->where('category_id', request('category_id'));
        }

        // Price filter
        if (request('min_price')) {
            $query->where('price', '>=', request('min_price'));
        }
        if (request('max_price')) {
            $query->where('price', '<=', request('max_price'));
        }

        // Sorting
        switch (request('sort', 'newest')) {
            case 'price_asc': $query->orderBy('price', 'asc'); break;
            case 'price_desc': $query->orderBy('price', 'desc'); break;
            case 'popular': $query->orderBy('sales_count', 'desc'); break;
            case 'rating': $query->orderBy('average_rating', 'desc'); break;
            default: $query->orderBy('created_at', 'desc');
        }

        // Featured
        if (request('featured')) {
            $query->where('is_featured', true);
        }

        $limit = min(request('limit', 20), 50);
        $products = $query->paginate($limit);

        return response()->json([
            'success' => true,
            'data' => $products->items(),
            'meta' => [
                'total' => $products->total(),
                'page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
            ],
        ]);
    });

    // Single Product
    Route::get('/products/{productId}', function ($storeSlug, $productId) {
        $store = \App\Models\Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();

        $product = \App\Models\Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->with(['images', 'category', 'variants', 'reviews' => function ($q) {
                $q->where('is_approved', true)->latest()->limit(10);
            }])
            ->findOrFail($productId);

        // Increment view count
        $product->increment('views_count');

        // Related products
        $related = \App\Models\Product::where('store_id', $store->id)
            ->where('is_active', true)
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->limit(8)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $product,
            'related' => $related,
        ]);
    });

    // Categories
    Route::get('/categories', function ($storeSlug) {
        $store = \App\Models\Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();

        $categories = \App\Models\Category::where('store_id', $store->id)
            ->where('is_active', true)
            ->withCount(['products' => fn($q) => $q->where('is_active', true)])
            ->orderBy('sort_order')
            ->get();

        return response()->json(['success' => true, 'data' => $categories]);
    });

    // Single Category
    Route::get('/categories/{categoryId}', function ($storeSlug, $categoryId) {
        $store = \App\Models\Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();

        $category = \App\Models\Category::where('store_id', $store->id)
            ->where('is_active', true)
            ->findOrFail($categoryId);

        return response()->json(['success' => true, 'data' => $category]);
    });

    // Shipping - Get available companies and rates
    Route::get('/shipping/companies', function ($storeSlug) {
        $store = \App\Models\Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();

        $companies = \App\Models\ShippingCompany::where('store_id', $store->id)
            ->where('is_active', true)
            ->get(['id', 'name', 'logo', 'estimated_days']);

        return response()->json(['success' => true, 'data' => $companies]);
    });

    // Shipping - Calculate rate
    Route::post('/shipping/calculate', function ($storeSlug) {
        $store = \App\Models\Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();

        $validated = request()->validate([
            'wilaya_id' => 'required|integer',
            'commune_id' => 'nullable|integer',
            'shipping_company_id' => 'required|integer',
            'total_weight' => 'nullable|numeric',
            'order_total' => 'nullable|numeric',
            'shipping_type' => 'nullable|string|in:home,desk',
        ]);

        $rate = \App\Models\ShippingRate::where('store_id', $store->id)
            ->where('shipping_company_id', $validated['shipping_company_id'])
            ->where('wilaya_id', $validated['wilaya_id'])
            ->when($validated['commune_id'] ?? null, fn($q, $v) => $q->where('commune_id', $v))
            ->where('is_active', true)
            ->first();

        if (!$rate) {
            // Try default rate for this company
            $rate = \App\Models\ShippingRate::where('store_id', $store->id)
                ->where('shipping_company_id', $validated['shipping_company_id'])
                ->whereNull('wilaya_id')
                ->where('is_active', true)
                ->first();
        }

        $cost = $rate ? ($validated['shipping_type'] === 'desk' ? ($rate->desk_price ?? $rate->home_price) : $rate->home_price) : 0;

        // Check free shipping rules
        $freeShippingRule = \App\Models\FreeShippingRule::where('store_id', $store->id)
            ->where('is_active', true)
            ->where(function ($q) use ($validated) {
                $q->whereNull('wilaya_id')
                  ->orWhere('wilaya_id', $validated['wilaya_id']);
            })
            ->first();

        $isFreeShipping = false;
        if ($freeShippingRule && ($validated['order_total'] ?? 0) >= $freeShippingRule->min_order_amount) {
            $isFreeShipping = true;
            $cost = 0;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'cost' => $cost,
                'is_free' => $isFreeShipping,
                'estimated_days' => $rate?->estimated_days,
                'free_shipping_min' => $freeShippingRule?->min_order_amount,
            ],
        ]);
    });

    // Wilayas list
    Route::get('/wilayas', function () {
        $wilayas = \App\Models\Wilaya::orderBy('code')->get(['id', 'code', 'name_ar', 'name_fr', 'name_en']);
        return response()->json(['success' => true, 'data' => $wilayas]);
    });

    // Communes by wilaya
    Route::get('/wilayas/{wilayaId}/communes', function ($storeSlug, $wilayaId) {
        $communes = \App\Models\Commune::where('wilaya_id', $wilayaId)
            ->orderBy('name_ar')
            ->get(['id', 'name_ar', 'name_fr', 'name_en', 'zip_code']);
        return response()->json(['success' => true, 'data' => $communes]);
    });

    // Place Order
    Route::post('/orders', function ($storeSlug) {
        $store = \App\Models\Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();

        $validated = request()->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_phone2' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email',
            'wilaya_id' => 'required|integer',
            'commune_id' => 'nullable|integer',
            'shipping_address' => 'required|string',
            'shipping_company_id' => 'required|integer',
            'shipping_type' => 'nullable|string|in:home,desk',
            'notes' => 'nullable|string',
            'coupon_code' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Find or create customer
        $customer = \App\Models\Customer::firstOrCreate(
            ['store_id' => $store->id, 'phone' => $validated['customer_phone']],
            [
                'name' => $validated['customer_name'],
                'email' => $validated['customer_email'] ?? null,
                'wilaya_id' => $validated['wilaya_id'],
                'commune_id' => $validated['commune_id'] ?? null,
                'address' => $validated['shipping_address'],
            ]
        );

        // Calculate totals
        $subtotal = 0;
        $orderItems = [];
        foreach ($validated['items'] as $item) {
            $product = \App\Models\Product::where('store_id', $store->id)->findOrFail($item['product_id']);
            $price = $product->sale_price ?? $product->price;

            if (isset($item['variant_id'])) {
                $variant = $product->variants()->find($item['variant_id']);
                if ($variant) {
                    $price = $variant->sale_price ?? $variant->price ?? $price;
                }
            }

            $orderItems[] = [
                'product_id' => $product->id,
                'variant_id' => $item['variant_id'] ?? null,
                'product_name' => $product->name_ar,
                'quantity' => $item['quantity'],
                'price' => $price,
                'total' => $price * $item['quantity'],
            ];
            $subtotal += $price * $item['quantity'];
        }

        // Calculate shipping
        $shippingCost = 0;
        // (simplified - use ShippingService in production)

        // Apply coupon
        $discount = 0;
        if (!empty($validated['coupon_code'])) {
            $coupon = \App\Models\Coupon::where('store_id', $store->id)
                ->where('code', $validated['coupon_code'])
                ->where('is_active', true)
                ->first();
            if ($coupon) {
                $discount = $coupon->type === 'percentage'
                    ? ($subtotal * $coupon->value / 100)
                    : $coupon->value;
                if ($coupon->max_discount) {
                    $discount = min($discount, $coupon->max_discount);
                }
            }
        }

        $total = $subtotal - $discount + $shippingCost;

        // Create order
        $order = \App\Models\Order::create([
            'store_id' => $store->id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-' . strtoupper(uniqid()),
            'customer_name' => $validated['customer_name'],
            'customer_phone' => $validated['customer_phone'],
            'customer_phone2' => $validated['customer_phone2'] ?? null,
            'customer_email' => $validated['customer_email'] ?? null,
            'wilaya_id' => $validated['wilaya_id'],
            'commune_id' => $validated['commune_id'] ?? null,
            'shipping_address' => $validated['shipping_address'],
            'shipping_company_id' => $validated['shipping_company_id'],
            'shipping_type' => $validated['shipping_type'] ?? 'home',
            'subtotal' => $subtotal,
            'shipping_cost' => $shippingCost,
            'discount' => $discount,
            'total_amount' => $total,
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
            'source' => 'storefront',
        ]);

        // Create order items
        foreach ($orderItems as $item) {
            $order->items()->create($item);
        }

        // Track pixel events
        if (function_exists('fbq')) {
            // This is handled on frontend
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total_amount,
                'status' => $order->status,
            ],
            'message' => 'تم إنشاء الطلب بنجاح',
        ], 201);
    });

    // Track Order
    Route::get('/orders/track/{orderNumber}', function ($storeSlug, $orderNumber) {
        $store = \App\Models\Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();

        $order = \App\Models\Order::where('store_id', $store->id)
            ->where(function ($q) use ($orderNumber) {
                $q->where('order_number', $orderNumber)
                  ->orWhere('id', $orderNumber);
            })
            ->with(['items', 'statusHistory'])
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'customer_name' => $order->customer_name,
                'customer_phone' => $order->customer_phone,
                'shipping_address' => $order->shipping_address,
                'subtotal' => $order->subtotal,
                'shipping_cost' => $order->shipping_cost,
                'total_amount' => $order->total_amount,
                'items' => $order->items,
                'status_history' => $order->statusHistory,
                'created_at' => $order->created_at,
            ],
        ]);
    });

    // Reviews for a product
    Route::get('/products/{productId}/reviews', function ($storeSlug, $productId) {
        $store = \App\Models\Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();

        $reviews = \App\Models\Review::where('store_id', $store->id)
            ->where('product_id', $productId)
            ->where('is_approved', true)
            ->latest()
            ->paginate(10);

        return response()->json(['success' => true, 'data' => $reviews->items()]);
    });

    // Submit Review
    Route::post('/products/{productId}/reviews', function ($storeSlug, $productId) {
        $store = \App\Models\Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();

        $validated = request()->validate([
            'customer_name' => 'required|string|max:255',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = \App\Models\Review::create([
            'store_id' => $store->id,
            'product_id' => $productId,
            'customer_name' => $validated['customer_name'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'] ?? null,
            'is_approved' => false, // Requires approval
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال التقييم وسيتم مراجعته',
        ], 201);
    });

    // Pages
    Route::get('/pages/{slug}', function ($storeSlug, $slug) {
        $store = \App\Models\Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();

        $page = \App\Models\StorePage::where('store_id', $store->id)
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return response()->json(['success' => true, 'data' => $page]);
    });

    // Validate Coupon
    Route::post('/coupons/validate', function ($storeSlug) {
        $store = \App\Models\Store::where('slug', $storeSlug)->where('is_active', true)->firstOrFail();

        $validated = request()->validate([
            'code' => 'required|string',
            'order_total' => 'nullable|numeric',
        ]);

        $coupon = \App\Models\Coupon::where('store_id', $store->id)
            ->where('code', $validated['code'])
            ->where('is_active', true)
            ->first();

        if (!$coupon) {
            return response()->json(['success' => false, 'message' => 'كود غير صالح'], 404);
        }

        // Check expiry
        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json(['success' => false, 'message' => 'الكود منتهي الصلاحية'], 400);
        }

        // Check usage limit
        if ($coupon->max_uses && $coupon->used_count >= $coupon->max_uses) {
            return response()->json(['success' => false, 'message' => 'تم استنفاد الكود'], 400);
        }

        // Check minimum order
        if ($coupon->min_order_amount && ($validated['order_total'] ?? 0) < $coupon->min_order_amount) {
            return response()->json([
                'success' => false,
                'message' => "الحد الأدنى للطلب هو {$coupon->min_order_amount} دج",
            ], 400);
        }

        $discount = $coupon->type === 'percentage'
            ? (($validated['order_total'] ?? 0) * $coupon->value / 100)
            : $coupon->value;

        if ($coupon->max_discount) {
            $discount = min($discount, $coupon->max_discount);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'discount' => round($discount, 2),
            ],
        ]);
    });
});
