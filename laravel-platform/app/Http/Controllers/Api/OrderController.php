<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Store;
use App\Models\Wilaya;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    /**
     * Create new order (public - for customers)
     */
    public function store(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)
            ->orWhere('slug', $storeId)
            ->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        // Check order limit
        if (!$store->canCreateOrder()) {
            return response()->json([
                'success' => false,
                'message' => 'المتجر وصل للحد الأقصى من الطلبات',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_email' => 'nullable|email',
            'wilaya_id' => 'required|exists:wilayas,id',
            'commune_id' => 'nullable|exists:communes,id',
            'address' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.variant_id' => 'nullable|exists:product_variants,id',
            'shipping_method' => 'nullable|string',
            'shipping_price' => 'nullable|numeric|min:0',
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
                ]
            );

            // Update customer info
            $customer->update([
                'name' => $request->customer_name,
                'email' => $request->customer_email,
            ]);

            // Calculate totals
            $subtotal = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('store_id', $store->id)
                    ->first();

                if (!$product) {
                    throw new \Exception("المنتج غير موجود: {$item['product_id']}");
                }

                $price = $product->sale_price ?? $product->price;
                $quantity = $item['quantity'];
                $itemTotal = $price * $quantity;
                $subtotal += $itemTotal;

                $itemsData[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'price' => $price,
                    'total' => $itemTotal,
                    'variant_id' => $item['variant_id'] ?? null,
                ];

                // Check stock
                if ($product->track_inventory && $product->stock_quantity < $quantity) {
                    throw new \Exception("الكمية المطلوبة غير متوفرة للمنتج: {$product->name}");
                }
            }

            // Get wilaya name
            $wilaya = Wilaya::find($request->wilaya_id);

            // Generate order number
            $orderNumber = 'ORD-' . strtoupper(Str::random(8));
            while (Order::where('order_number', $orderNumber)->exists()) {
                $orderNumber = 'ORD-' . strtoupper(Str::random(8));
            }

            $shippingPrice = $request->shipping_price ?? 0;
            $total = $subtotal + $shippingPrice;

            // Create order
            $order = Order::create([
                'store_id' => $store->id,
                'customer_id' => $customer->id,
                'order_number' => $orderNumber,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => 'cod',
                'subtotal' => $subtotal,
                'discount_amount' => 0,
                'shipping_price' => $shippingPrice,
                'total' => $total,
                'shipping_name' => $request->customer_name,
                'shipping_phone' => $request->customer_phone,
                'shipping_wilaya_id' => $request->wilaya_id,
                'shipping_wilaya' => $wilaya->name_ar ?? '',
                'shipping_commune_id' => $request->commune_id,
                'shipping_address' => $request->address,
                'shipping_method' => $request->shipping_method,
                'notes' => $request->notes,
            ]);

            // Create order items
            foreach ($itemsData as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product']->id,
                    'product_name' => $item['product']->name_ar ?? $item['product']->name,
                    'variant_id' => $item['variant_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['total'],
                ]);

                // Decrease stock
                if ($item['product']->track_inventory) {
                    $item['product']->decrement('stock_quantity', $item['quantity']);
                }
                $item['product']->increment('sold_count', $item['quantity']);
            }

            // Update store stats
            $store->increment('orders_count');
            $customer->increment('orders_count');
            $customer->increment('total_spent', $total);

            DB::commit();

            $order->load('items');

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الطلب بنجاح',
                'data' => [
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
     */
    public function track(Request $request, string $storeId, string $orderNumber): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $order = Order::where('store_id', $store->id)
            ->where('order_number', $orderNumber)
            ->with('items')
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
                'tracking_number' => $order->tracking_number,
                'items' => $order->items->map(fn($item) => [
                    'name' => $item->product_name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                ]),
                'created_at' => $order->created_at,
                'delivered_at' => $order->delivered_at,
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD METHODS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get orders for dashboard
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
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
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

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    /**
     * Get order stats
     */
    public function stats(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $stats = [
            'total_orders' => Order::where('store_id', $store->id)->count(),
            'pending_orders' => Order::where('store_id', $store->id)->where('status', 'pending')->count(),
            'confirmed_orders' => Order::where('store_id', $store->id)->where('status', 'confirmed')->count(),
            'shipped_orders' => Order::where('store_id', $store->id)->where('status', 'shipped')->count(),
            'delivered_orders' => Order::where('store_id', $store->id)->where('status', 'delivered')->count(),
            'cancelled_orders' => Order::where('store_id', $store->id)->where('status', 'cancelled')->count(),
            'total_revenue' => Order::where('store_id', $store->id)
                ->where('status', 'delivered')
                ->sum('total'),
            'today_orders' => Order::where('store_id', $store->id)
                ->whereDate('created_at', today())
                ->count(),
            'today_revenue' => Order::where('store_id', $store->id)
                ->whereDate('created_at', today())
                ->where('status', 'delivered')
                ->sum('total'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Get single order
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $order = Order::where('store_id', $store->id)
            ->where('id', $id)
            ->with(['items.product', 'customer'])
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
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $order = Order::where('store_id', $store->id)->where('id', $id)->first();

        if (!$order) {
            return response()->json(['success' => false, 'message' => 'الطلب غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled,returned',
            'tracking_number' => 'nullable|string|max:100',
            'shipping_company_id' => 'nullable|exists:shipping_companies,id',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $oldStatus = $order->status;
        $newStatus = $request->status;

        // Update order
        $order->update([
            'status' => $newStatus,
            'tracking_number' => $request->tracking_number ?? $order->tracking_number,
            'shipping_company_id' => $request->shipping_company_id ?? $order->shipping_company_id,
        ]);

        // Update timestamps
        if ($newStatus === 'confirmed' && !$order->confirmed_at) {
            $order->update(['confirmed_at' => now()]);
        } elseif ($newStatus === 'shipped' && !$order->shipped_at) {
            $order->update(['shipped_at' => now()]);
        } elseif ($newStatus === 'delivered' && !$order->delivered_at) {
            $order->update([
                'delivered_at' => now(),
                'payment_status' => 'paid',
            ]);
        } elseif ($newStatus === 'cancelled' && !$order->cancelled_at) {
            $order->update(['cancelled_at' => now()]);
            
            // Restore stock
            foreach ($order->items as $item) {
                if ($item->product && $item->product->track_inventory) {
                    $item->product->increment('stock_quantity', $item->quantity);
                }
            }
        }

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
            default => $status,
        };
    }
}
