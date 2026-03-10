<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    /**
     * قائمة الطلبات (للتاجر)
     */
    public function index(Request $request): JsonResponse
    {
        $storeId = $request->user()->store_id;

        $query = Order::with(['items', 'customer'])
            ->forStore($storeId)
            ->orderByDesc('created_at');

        // Filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 20);
        $orders = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $orders->items(),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'total' => $orders->total(),
            ]
        ]);
    }

    /**
     * عرض طلب
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $storeId = $request->user()->store_id;

        $order = Order::with(['items', 'customer', 'statusHistory.changedBy', 'shippingCompany'])
            ->forStore($storeId)
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $order
        ]);
    }

    /**
     * إنشاء طلب جديد (من الزبون)
     */
    public function store(Request $request): JsonResponse
    {
        $storeId = $request->route('storeId');

        $validator = Validator::make($request->all(), [
            'customer_name' => 'required|string|max:255',
            'customer_phone' => 'required|string|max:20',
            'customer_phone2' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email',
            'wilaya' => 'required|string',
            'commune' => 'nullable|string',
            'shipping_address' => 'required|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_notes' => 'nullable|string',
            'shipping_cost' => 'nullable|numeric|min:0',
        ], [
            'customer_name.required' => 'الاسم مطلوب',
            'customer_phone.required' => 'رقم الهاتف مطلوب',
            'wilaya.required' => 'الولاية مطلوبة',
            'shipping_address.required' => 'العنوان مطلوب',
            'items.required' => 'يجب اختيار منتج واحد على الأقل',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Calculate totals
            $subtotal = 0;
            $orderItems = [];

            foreach ($request->items as $item) {
                $product = Product::forStore($storeId)->findOrFail($item['product_id']);
                
                $unitPrice = $product->final_price;
                $totalPrice = $unitPrice * $item['quantity'];
                $subtotal += $totalPrice;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_name_ar' => $product->name_ar,
                    'product_image' => $product->primary_image,
                    'product_sku' => $product->sku,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];

                // Decrease stock
                $product->decrementStock($item['quantity']);
            }

            $shippingCost = $request->shipping_cost ?? 0;
            $total = $subtotal + $shippingCost;

            // Find or create customer
            $customer = Customer::findOrCreateByPhone($storeId, $request->customer_phone, [
                'name' => $request->customer_name,
                'email' => $request->customer_email,
                'phone2' => $request->customer_phone2,
                'wilaya' => $request->wilaya,
                'commune' => $request->commune,
                'address' => $request->shipping_address,
            ]);

            // Create order
            $order = Order::create([
                'store_id' => $storeId,
                'customer_id' => $customer->id,
                'order_number' => Order::generateOrderNumber($storeId),
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_phone2' => $request->customer_phone2,
                'customer_email' => $request->customer_email,
                'wilaya' => $request->wilaya,
                'commune' => $request->commune,
                'shipping_address' => $request->shipping_address,
                'subtotal' => $subtotal,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'status' => Order::STATUS_PENDING,
                'customer_notes' => $request->customer_notes,
                'source' => $request->source ?? 'website',
                'utm_source' => $request->utm_source,
                'utm_medium' => $request->utm_medium,
                'utm_campaign' => $request->utm_campaign,
            ]);

            // Create order items
            foreach ($orderItems as $item) {
                $order->items()->create($item);
            }

            // Update customer stats
            $customer->updateFromOrder($order);

            // Update store stats
            $order->store->incrementOrdersCount();
            $order->store->increment('total_revenue', $total);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'تم إنشاء الطلب بنجاح',
                'data' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'status' => $order->status,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'حدث خطأ أثناء إنشاء الطلب',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * تحديث حالة الطلب
     */
    public function updateStatus(Request $request, string $id): JsonResponse
    {
        $storeId = $request->user()->store_id;

        $order = Order::forStore($storeId)->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:' . implode(',', array_keys(Order::STATUSES)),
            'notes' => 'nullable|string',
            'tracking_number' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        if ($request->tracking_number) {
            $order->update(['tracking_number' => $request->tracking_number]);
        }

        $order->updateStatus($request->status, $request->notes, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الطلب',
            'data' => $order->fresh()
        ]);
    }

    /**
     * إحصائيات الطلبات
     */
    public function stats(Request $request): JsonResponse
    {
        $storeId = $request->user()->store_id;

        $stats = [
            'total' => Order::forStore($storeId)->count(),
            'pending' => Order::forStore($storeId)->pending()->count(),
            'confirmed' => Order::forStore($storeId)->confirmed()->count(),
            'processing' => Order::forStore($storeId)->processing()->count(),
            'shipped' => Order::forStore($storeId)->shipped()->count(),
            'delivered' => Order::forStore($storeId)->delivered()->count(),
            'cancelled' => Order::forStore($storeId)->cancelled()->count(),
            'today' => Order::forStore($storeId)->today()->count(),
            'this_month' => Order::forStore($storeId)->thisMonth()->count(),
            'revenue_today' => Order::forStore($storeId)->today()->delivered()->sum('total'),
            'revenue_this_month' => Order::forStore($storeId)->thisMonth()->delivered()->sum('total'),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }
}
