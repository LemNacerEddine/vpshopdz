<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderStatusHistory;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Store;
use App\Models\Wilaya;
use App\Models\Coupon;
use App\Models\AbandonedCart;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // PUBLIC ROUTES (Storefront)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Create new order (public - for customers)
     * @route POST /api/v1/store/{store}/orders
     */
    public function store(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)
            ->orWhere('slug', $storeId)
            ->first();

        if (!$store || !$store->isAccessible()) {
            return response()->json(['success' => false, 'message' => 'المتجر غير متاح'], 404);
        }

        // Check order limit
        if (!$store->canCreateOrder()) {
            return response()->json([
                'success' => false,
                'message' => 'المتجر وصل للحد الأقصى من الطلبات لهذا الشهر',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_phone2' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email',
            'wilaya_id' => 'required|exists:wilayas,id',
            'commune_id' => 'nullable|exists:communes,id',
            'address' => 'required|string|max:500',
            'delivery_type' => 'required|in:home,office,pickup',
            'shipping_company_id' => 'nullable|exists:shipping_companies,id',
            'shipping_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'coupon_code' => 'nullable|string',
            'source' => 'nullable|string',
            'utm_source' => 'nullable|string',
            'utm_medium' => 'nullable|string',
            'utm_campaign' => 'nullable|string',
            'recovery_token' => 'nullable|string',
        ], [
            'customer_name.required' => 'الاسم مطلوب',
            'customer_phone.required' => 'رقم الهاتف مطلوب',
            'wilaya_id.required' => 'الولاية مطلوبة',
            'address.required' => 'العنوان مطلوب',
            'items.required' => 'يجب إضافة منتج واحد على الأقل',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Get or create customer
            $customer = Customer::firstOrCreate(
                [
                    'store_id' => $store->id,
                    'phone' => $request->customer_phone,
                ],
                [
                    'name' => $request->customer_name,
                    'email' => $request->customer_email,
                    'phone2' => $request->customer_phone2,
                    'wilaya_id' => $request->wilaya_id,
                    'commune_id' => $request->commune_id,
                    'address' => $request->address,
                ]
            );

            // Update customer info
            $customer->update([
                'name' => $request->customer_name,
                'email' => $request->customer_email,
                'wilaya_id' => $request->wilaya_id,
                'commune_id' => $request->commune_id,
                'address' => $request->address,
            ]);

            // Calculate totals
            $subtotal = 0;
            $totalWeight = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $store->id)
                    ->where('status', 'active')
                    ->first();

                if (!$product) {
                    throw new \Exception("المنتج غير متوفر: {$item['product_id']}");
                }

                $price = $product->sale_price ?? $product->price;
                $quantity = $item['quantity'];
                $itemTotal = $price * $quantity;
                $subtotal += $itemTotal;
                $totalWeight += ($product->weight ?? 0) * $quantity;

                $itemsData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'price' => $price,
                    'original_price' => $product->price,
                    'total' => $itemTotal,
                    'variant_id' => $item['variant_id'] ?? null,
                    'weight' => $product->weight ?? 0,
                ];

                // Check stock
                if ($product->track_inventory && $product->stock_quantity < $quantity) {
                    throw new \Exception("الكمية المطلوبة غير متوفرة للمنتج: {$product->name}");
                }
            }

            // Apply coupon
            $discount = 0;
            $couponId = null;

            if ($request->coupon_code) {
                $coupon = Coupon::where('store_id', $store->id)
                    ->where('code', strtoupper($request->coupon_code))
                    ->where('is_active', true)
                    ->first();

                if ($coupon && $coupon->isValid()) {
                    $discount = $coupon->calculateDiscount($subtotal);
                    $couponId = $coupon->id;
                    $coupon->increment('used_count');
                }
            }

            // Get wilaya name
            $wilaya = Wilaya::find($request->wilaya_id);

            // Generate order number
            $prefix = strtoupper(substr($store->slug ?? 'ORD', 0, 3));
            $date = now()->format('ymd');
            $count = Order::where('store_id', $store->id)->whereDate('created_at', today())->count() + 1;
            $orderNumber = "{$prefix}-{$date}-" . str_pad($count, 4, '0', STR_PAD_LEFT);

            $total = $subtotal - $discount + $request->shipping_price;

            // Create order
            $order = Order::create([
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'cod',
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'shipping_price' => $request->shipping_price,
                'total' => $total,
                'total_weight' => $totalWeight,
                'coupon_id' => $couponId,
                'shipping_name' => $request->customer_name,
                'shipping_phone' => $request->customer_phone,
                'shipping_phone2' => $request->customer_phone2,
                'shipping_wilaya_id' => $request->wilaya_id,
                'shipping_wilaya' => $wilaya->name_ar ?? '',
                'shipping_commune_id' => $request->commune_id,
                'shipping_address' => $request->address,
                'delivery_type' => $request->delivery_type,
                'shipping_company_id' => $request->shipping_company_id,
                'shipping_method' => $request->delivery_type,
                'notes' => $request->notes,
                'source' => $request->source ?? 'web',
                'utm_source' => $request->utm_source,
                'utm_medium' => $request->utm_medium,
                'utm_campaign' => $request->utm_campaign,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            // Create order items
            foreach ($itemsData as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'product_name_ar' => $item['product']->name_ar,
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'original_price' => $item['original_price'],
                    'total' => $item['total'],
                    'weight' => $item['weight'],
                ]);

                // Decrease stock
                if ($item['product']->track_inventory) {
                    $item['product']->decrement('stock_quantity', $item['quantity']);
                }
                $item['product']->increment('sold_count', $item['quantity']);
            }

            // Record status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => null,
                'to_status' => 'pending',
                'notes' => 'طلب جديد من الموقع',
            ]);

            // Update store stats
            $store->increment('orders_count');
            $store->increment('total_revenue', $total);
            $customer->increment('orders_count');
            $customer->increment('total_spent', $total);
            $customer->update(['last_order_at' => now()]);

            // Mark abandoned cart as recovered
            if ($request->recovery_token) {
                AbandonedCart::where('store_id', $store->id)
                    ->where('recovery_token', $request->recovery_token)
                    ->where('status', 'active')
                    ->first()
                    ?->markRecovered($order->id);
            }

            DB::commit();

            $order->load('items');

            // TODO: Send notification (WhatsApp/Telegram) to store owner
            // TODO: Fire event for pixel tracking

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الطلب بنجاح',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'status' => $order->status,
                    'items_count' => $order->items->count(),
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Track order (public)
     * @route GET /api/v1/store/{store}/orders/track
     */
    public function track(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'order_number' => 'required|string',
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $order = Order::where('store_id', $store->id)
            ->where('order_number', $request->order_number)
            ->where('shipping_phone', $request->phone)
            ->with(['items', 'statusHistory'])
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'الطلب غير موجود'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order_number' => $order->order_number,
                'status' => $order->status,
                'status_label' => $this->getStatusLabel($order->status),
                'total' => $order->total,
                'shipping_price' => $order->shipping_price,
                'shipping_wilaya' => $order->shipping_wilaya,
                'shipping_address' => $order->shipping_address,
                'delivery_type' => $order->delivery_type,
                'tracking_number' => $order->tracking_number,
                'items' => $order->items->map(fn($item) => [
                    'name' => $item->product_name_ar ?? $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                ]),
                'history' => $order->statusHistory->map(fn($h) => [
                    'from' => $h->from_status,
                    'to' => $h->to_status,
                    'to_label' => $this->getStatusLabel($h->to_status),
                    'notes' => $h->notes,
                    'date' => $h->created_at->format('Y-m-d H:i'),
                ]),
                'created_at' => $order->created_at->format('Y-m-d H:i'),
                'delivered_at' => $order->delivered_at?->format('Y-m-d H:i'),
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD METHODS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get orders for dashboard
     * @route GET /api/v1/dashboard/orders
     */
    public function dashboardIndex(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $query = Order::where('store_id', $store->id)
            ->with(['items', 'customer']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by delivery type
        if ($request->has('delivery_type')) {
            $query->where('delivery_type', $request->delivery_type);
        }

        // Filter by wilaya
        if ($request->has('wilaya_id')) {
            $query->where('shipping_wilaya_id', $request->wilaya_id);
        }

        // Filter by date
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        // Filter by source
        if ($request->has('source')) {
            $query->where('source', $request->source);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('shipping_phone', 'like', "%{$search}%")
                  ->orWhere('shipping_name', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $orders = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get order statistics
     * @route GET /api/v1/dashboard/orders/stats
     */
    public function stats(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $period = $request->get('period', 'month');
        $startDate = match($period) {
            'today' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            'all' => null,
            default => now()->startOfMonth(),
        };

        $baseQuery = Order::where('store_id', $store->id);
        if ($startDate) {
            $baseQuery->where('created_at', '>=', $startDate);
        }

        $stats = [
            'total_orders' => (clone $baseQuery)->count(),
            'pending' => (clone $baseQuery)->where('status', 'pending')->count(),
            'confirmed' => (clone $baseQuery)->where('status', 'confirmed')->count(),
            'processing' => (clone $baseQuery)->where('status', 'processing')->count(),
            'shipped' => (clone $baseQuery)->where('status', 'shipped')->count(),
            'delivered' => (clone $baseQuery)->where('status', 'delivered')->count(),
            'cancelled' => (clone $baseQuery)->where('status', 'cancelled')->count(),
            'returned' => (clone $baseQuery)->where('status', 'returned')->count(),
            'total_revenue' => (clone $baseQuery)->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])->sum('total'),
            'average_order_value' => (clone $baseQuery)->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])->avg('total') ?? 0,
            'total_shipping' => (clone $baseQuery)->sum('shipping_price'),
            'total_discount' => (clone $baseQuery)->sum('discount_amount'),
            'today_orders' => Order::where('store_id', $store->id)->whereDate('created_at', today())->count(),
            'today_revenue' => Order::where('store_id', $store->id)->whereDate('created_at', today())
                ->whereIn('status', ['confirmed', 'processing', 'shipped', 'delivered'])->sum('total'),
        ];

        // Confirmation rate
        $stats['confirmation_rate'] = $stats['total_orders'] > 0
            ? round(($stats['delivered'] / $stats['total_orders']) * 100, 1)
            : 0;

        // Daily breakdown for chart
        $dailyQuery = Order::where('store_id', $store->id);
        if ($startDate) {
            $dailyQuery->where('created_at', '>=', $startDate);
        }
        $stats['daily_breakdown'] = $dailyQuery
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top products
        $stats['top_products'] = OrderItem::whereHas('order', function ($q) use ($store, $startDate) {
            $q->where('store_id', $store->id)
              ->whereNotIn('status', ['cancelled', 'returned']);
            if ($startDate) $q->where('created_at', '>=', $startDate);
        })
            ->selectRaw('product_id, product_name, SUM(quantity) as total_quantity, SUM(total) as total_revenue')
            ->groupBy('product_id', 'product_name')
            ->orderByDesc('total_quantity')
            ->limit(10)
            ->get();

        // Top wilayas
        $topWilayasQuery = Order::where('store_id', $store->id)
            ->whereNotIn('status', ['cancelled', 'returned']);
        if ($startDate) $topWilayasQuery->where('created_at', '>=', $startDate);
        $stats['top_wilayas'] = $topWilayasQuery
            ->selectRaw('shipping_wilaya_id, shipping_wilaya, COUNT(*) as orders_count, SUM(total) as total_revenue')
            ->groupBy('shipping_wilaya_id', 'shipping_wilaya')
            ->orderByDesc('orders_count')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stats,
            'period' => $period,
        ]);
    }

    /**
     * Get single order
     * @route GET /api/v1/dashboard/orders/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $order = Order::where('store_id', $store->id)
            ->where('id', $id)
            ->with(['items.product', 'customer', 'statusHistory.changedByUser', 'coupon'])
            ->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'الطلب غير موجود'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $order,
        ]);
    }

    /**
     * Update order status
     * @route PUT /api/v1/dashboard/orders/{id}/status
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $order = Order::where('store_id', $store->id)->where('id', $id)->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'الطلب غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,returned,refunded',
            'tracking_number' => 'nullable|string|max:100',
            'shipping_company_id' => 'nullable|exists:shipping_companies,id',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Validate status transition
        if (!$this->isValidStatusTransition($oldStatus, $newStatus)) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن تغيير الحالة من {$this->getStatusLabel($oldStatus)} إلى {$this->getStatusLabel($newStatus)}",
            ], 400);
        }

        // Update order
        $updateData = [
            'status' => $newStatus,
            'tracking_number' => $request->tracking_number ?? $order->tracking_number,
            'shipping_company_id' => $request->shipping_company_id ?? $order->shipping_company_id,
        ];

        // Update timestamps
        if ($newStatus === 'confirmed' && !$order->confirmed_at) {
            $updateData['confirmed_at'] = now();
        } elseif ($newStatus === 'shipped' && !$order->shipped_at) {
            $updateData['shipped_at'] = now();
        } elseif ($newStatus === 'delivered' && !$order->delivered_at) {
            $updateData['delivered_at'] = now();
            $updateData['payment_status'] = 'paid';
        } elseif ($newStatus === 'cancelled' && !$order->cancelled_at) {
            $updateData['cancelled_at'] = now();
        }

        $order->update($updateData);

        // Restore stock on cancellation
        if ($newStatus === 'cancelled') {
            foreach ($order->items as $item) {
                if ($item->product && $item->product->track_inventory) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }
        }

        // Record status history
        OrderStatusHistory::create([
            'order_id' => $order->id,
            'from_status' => $oldStatus,
            'to_status' => $newStatus,
            'notes' => $request->notes,
            'changed_by' => $request->user()->id,
        ]);

        // TODO: Send notification to customer about status change

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الطلب بنجاح',
            'data' => [
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'order' => $order->fresh(['items']),
            ],
        ]);
    }

    /**
     * Bulk update orders status
     * @route PUT /api/v1/dashboard/orders/bulk-status
     */
    public function bulkUpdateStatus(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        $validator = Validator::make($request->all(), [
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'required|string',
            'status' => 'required|in:confirmed,processing,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $updated = 0;
        $errors = [];

        foreach ($request->order_ids as $orderId) {
            $order = Order::where('store_id', $store->id)->where('id', $orderId)->first();

            if (!$order) {
                $errors[] = "الطلب {$orderId} غير موجود";
                continue;
            }

            if (!$this->isValidStatusTransition($order->status, $request->status)) {
                $errors[] = "لا يمكن تغيير حالة الطلب {$order->order_number}";
                continue;
            }

            $oldStatus = $order->status;
            $order->update(['status' => $request->status]);

            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $oldStatus,
                'to_status' => $request->status,
                'notes' => $request->notes ?? 'تحديث جماعي',
                'changed_by' => $request->user()->id,
            ]);

            if ($request->status === 'cancelled') {
                foreach ($order->items as $item) {
                    if ($item->product && $item->product->track_inventory) {
                        $item->product->increment('stock_quantity', $item->quantity);
                    }
                }
            }

            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => "تم تحديث {$updated} طلب",
            'data' => [
                'updated' => $updated,
                'errors' => $errors,
            ],
        ]);
    }

    /**
     * Export orders
     * @route GET /api/v1/dashboard/orders/export
     */
    public function export(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        $query = Order::where('store_id', $store->id)->with(['items']);

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        $exportData = $orders->map(function ($order) {
            return [
                'order_number' => $order->order_number,
                'date' => $order->created_at->format('Y-m-d H:i'),
                'customer_name' => $order->shipping_name,
                'customer_phone' => $order->shipping_phone,
                'wilaya' => $order->shipping_wilaya,
                'address' => $order->shipping_address,
                'delivery_type' => $order->delivery_type,
                'products' => $order->items->map(fn($i) => "{$i->product_name} x{$i->quantity}")->implode(' | '),
                'subtotal' => $order->subtotal,
                'shipping' => $order->shipping_price,
                'discount' => $order->discount_amount,
                'total' => $order->total,
                'status' => $this->getStatusLabel($order->status),
                'tracking_number' => $order->tracking_number,
                'source' => $order->source,
                'notes' => $order->notes,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $exportData,
            'count' => $exportData->count(),
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function isValidStatusTransition(string $from, string $to): bool
    {
        $validTransitions = [
            'pending' => ['confirmed', 'cancelled'],
            'confirmed' => ['processing', 'shipped', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['delivered', 'returned'],
            'delivered' => ['returned', 'refunded'],
            'cancelled' => [],
            'returned' => ['refunded'],
            'refunded' => [],
        ];

        return in_array($to, $validTransitions[$from] ?? []);
    }

    /**
     * Get status label in Arabic
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'pending' => 'قيد الانتظار',
            'confirmed' => 'مؤكد',
            'processing' => 'قيد التجهيز',
            'shipped' => 'تم الشحن',
            'delivered' => 'تم التسليم',
            'cancelled' => 'ملغي',
            'returned' => 'مرتجع',
            'refunded' => 'مسترجع',
            default => $status,
        };
    }
}
