<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomerController extends Controller
{
    /**
     * Get customers list
     * @route GET /api/v1/dashboard/customers
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $query = Customer::where('store_id', $store->id);

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by wilaya
        if ($request->has('wilaya_id')) {
            $query->where('wilaya_id', $request->wilaya_id);
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $customers = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => $customers->items(),
            'meta' => [
                'current_page' => $customers->currentPage(),
                'last_page' => $customers->lastPage(),
                'total' => $customers->total(),
            ],
        ]);
    }

    /**
     * Get single customer with order history
     * @route GET /api/v1/dashboard/customers/{id}
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $customer = Customer::where('store_id', $store->id)
            ->where('id', $id)
            ->first();

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'العميل غير موجود'], 404);
        }

        $orders = Order::where('store_id', $store->id)
            ->where('customer_id', $customer->id)
            ->with('items')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'customer' => $customer,
                'orders' => $orders,
                'stats' => [
                    'total_orders' => $customer->orders_count,
                    'total_spent' => $customer->total_spent,
                    'average_order' => $customer->orders_count > 0
                        ? round($customer->total_spent / $customer->orders_count, 2)
                        : 0,
                    'last_order_at' => $customer->last_order_at?->format('Y-m-d H:i'),
                ],
            ],
        ]);
    }

    /**
     * Update customer
     * @route PUT /api/v1/dashboard/customers/{id}
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $customer = Customer::where('store_id', $store->id)->where('id', $id)->first();

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'العميل غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone2' => 'nullable|string|max:20',
            'wilaya_id' => 'nullable|exists:wilayas,id',
            'commune_id' => 'nullable|exists:communes,id',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'is_blocked' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $customer->update($request->only([
            'name', 'email', 'phone2', 'wilaya_id', 'commune_id',
            'address', 'notes', 'tags', 'is_blocked',
        ]));

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث بيانات العميل',
            'data' => $customer->fresh(),
        ]);
    }

    /**
     * Get customer statistics
     * @route GET /api/v1/dashboard/customers/stats
     */
    public function stats(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $stats = [
            'total_customers' => Customer::where('store_id', $store->id)->count(),
            'new_this_month' => Customer::where('store_id', $store->id)
                ->where('created_at', '>=', now()->startOfMonth())
                ->count(),
            'returning_customers' => Customer::where('store_id', $store->id)
                ->where('orders_count', '>', 1)
                ->count(),
            'average_spent' => Customer::where('store_id', $store->id)
                ->where('orders_count', '>', 0)
                ->avg('total_spent') ?? 0,
            'top_customers' => Customer::where('store_id', $store->id)
                ->where('orders_count', '>', 0)
                ->orderByDesc('total_spent')
                ->limit(10)
                ->get(['id', 'name', 'phone', 'orders_count', 'total_spent']),
            'by_wilaya' => Customer::where('store_id', $store->id)
                ->selectRaw('wilaya_id, COUNT(*) as count')
                ->groupBy('wilaya_id')
                ->orderByDesc('count')
                ->limit(10)
                ->with('wilaya')
                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Export customers
     * @route GET /api/v1/dashboard/customers/export
     */
    public function export(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        $customers = Customer::where('store_id', $store->id)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($customer) {
                return [
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'wilaya' => $customer->wilaya->name_ar ?? '',
                    'address' => $customer->address,
                    'orders_count' => $customer->orders_count,
                    'total_spent' => $customer->total_spent,
                    'last_order' => $customer->last_order_at?->format('Y-m-d'),
                    'registered' => $customer->created_at->format('Y-m-d'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $customers,
            'count' => $customers->count(),
        ]);
    }

    /**
     * Delete customer
     * @route DELETE /api/v1/dashboard/customers/{id}
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;

        $customer = Customer::where('store_id', $store->id)->where('id', $id)->first();

        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'العميل غير موجود'], 404);
        }

        // Check if customer has orders
        $ordersCount = Order::where('customer_id', $customer->id)->count();

        if ($ordersCount > 0) {
            return response()->json([
                'success' => false,
                'message' => "لا يمكن حذف العميل لأنه لديه {$ordersCount} طلب. يمكنك حظره بدلاً من ذلك.",
            ], 400);
        }

        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف العميل',
        ]);
    }
}
