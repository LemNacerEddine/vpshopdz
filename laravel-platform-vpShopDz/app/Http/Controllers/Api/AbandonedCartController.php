<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AbandonedCart;
use App\Models\Store;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AbandonedCartController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // PUBLIC ROUTES (Storefront)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Save/update abandoned cart (called from storefront)
     * @route POST /api/v1/store/{store}/cart/save
     */
    public function save(Request $request, string $storeId): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'session_id' => 'required|string',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:20',
            'customer_email' => 'nullable|email',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.name' => 'nullable|string',
            'items.*.image' => 'nullable|string',
            'items.*.variant_id' => 'nullable|string',
            'source' => 'nullable|string',
            'utm_source' => 'nullable|string',
            'utm_medium' => 'nullable|string',
            'utm_campaign' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Calculate subtotal
        $subtotal = collect($request->items)->sum(function ($item) {
            return $item['price'] * $item['quantity'];
        });

        // Generate recovery token
        $recoveryToken = Str::random(32);

        $cart = AbandonedCart::updateOrCreate(
            [
                'store_id' => $store->id,
                'session_id' => $request->session_id,
                'status' => 'active',
            ],
            [
                'customer_name' => $request->customer_name,
                'customer_phone' => $request->customer_phone,
                'customer_email' => $request->customer_email,
                'items' => $request->items,
                'subtotal' => $subtotal,
                'recovery_token' => $recoveryToken,
                'recovery_url' => $store->getUrl() . '/cart/recover/' . $recoveryToken,
                'source' => $request->source ?? 'web',
                'utm_source' => $request->utm_source,
                'utm_medium' => $request->utm_medium,
                'utm_campaign' => $request->utm_campaign,
            ]
        );

        return response()->json([
            'success' => true,
            'data' => [
                'cart_id' => $cart->id,
                'recovery_token' => $recoveryToken,
            ],
        ]);
    }

    /**
     * Recover abandoned cart
     * @route GET /api/v1/store/{store}/cart/recover/{token}
     */
    public function recover(string $storeId, string $token): JsonResponse
    {
        $store = Store::where('id', $storeId)->orWhere('slug', $storeId)->first();

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'المتجر غير موجود'], 404);
        }

        $cart = AbandonedCart::where('store_id', $store->id)
            ->where('recovery_token', $token)
            ->where('status', 'active')
            ->first();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'السلة غير موجودة أو تم استرجاعها مسبقاً',
            ], 404);
        }

        // Validate that products still exist and are available
        $validItems = [];
        foreach ($cart->items as $item) {
            $product = Product::where('id', $item['product_id'])
                ->where('store_id', $store->id)
                ->where('status', 'active')
                ->first();

            if ($product) {
                $validItems[] = [
                    'product_id' => $product->id,
                    'name' => $product->display_name,
                    'price' => $product->final_price,
                    'quantity' => $item['quantity'],
                    'image' => $product->primary_image,
                    'in_stock' => $product->isInStock(),
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $validItems,
                'original_subtotal' => $cart->subtotal,
                'customer' => [
                    'name' => $cart->customer_name,
                    'phone' => $cart->customer_phone,
                    'email' => $cart->customer_email,
                ],
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // DASHBOARD ROUTES (Store Owner)
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get abandoned carts list
     * @route GET /api/v1/dashboard/abandoned-carts
     */
    public function index(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        // Check feature access
        if (!$store->canUseFeature('abandoned_cart')) {
            return response()->json([
                'success' => false,
                'message' => 'هذه الميزة غير متوفرة في خطتك الحالية',
                'upgrade_required' => true,
            ], 403);
        }

        $query = AbandonedCart::where('store_id', $store->id);

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

        // Search by phone
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_phone', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%");
            });
        }

        $carts = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $carts->items(),
            'meta' => [
                'current_page' => $carts->currentPage(),
                'last_page' => $carts->lastPage(),
                'total' => $carts->total(),
            ],
        ]);
    }

    /**
     * Get abandoned cart stats
     * @route GET /api/v1/dashboard/abandoned-carts/stats
     */
    public function stats(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $stats = [
            'total_abandoned' => AbandonedCart::where('store_id', $store->id)->count(),
            'active' => AbandonedCart::where('store_id', $store->id)->where('status', 'active')->count(),
            'recovered' => AbandonedCart::where('store_id', $store->id)->where('status', 'recovered')->count(),
            'total_value' => AbandonedCart::where('store_id', $store->id)->where('status', 'active')->sum('subtotal'),
            'recovered_value' => AbandonedCart::where('store_id', $store->id)->where('status', 'recovered')->sum('subtotal'),
            'recovery_rate' => 0,
            'today_abandoned' => AbandonedCart::where('store_id', $store->id)->whereDate('created_at', today())->count(),
            'this_week_abandoned' => AbandonedCart::where('store_id', $store->id)->where('created_at', '>=', now()->startOfWeek())->count(),
        ];

        $total = $stats['total_abandoned'];
        if ($total > 0) {
            $stats['recovery_rate'] = round(($stats['recovered'] / $total) * 100, 1);
        }

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    /**
     * Send reminder for abandoned cart
     * @route POST /api/v1/dashboard/abandoned-carts/{id}/remind
     */
    public function sendReminder(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $cart = AbandonedCart::where('store_id', $store->id)->where('id', $id)->first();

        if (!$cart) {
            return response()->json(['success' => false, 'message' => 'السلة غير موجودة'], 404);
        }

        if (!$cart->canSendReminder()) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكن إرسال تذكير لهذه السلة',
                'reason' => $this->getReminderBlockReason($cart),
            ], 400);
        }

        // Get WhatsApp integration
        $whatsappIntegration = $store->getIntegration('whatsapp_green_api') 
                            ?? $store->getIntegration('whatsapp_business');

        if (!$whatsappIntegration) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم تفعيل تكامل واتساب',
            ], 400);
        }

        // Get notification template
        $template = $store->notificationTemplates()
            ->where('channel', 'whatsapp')
            ->where('event', 'abandoned_cart')
            ->where('is_active', true)
            ->first();

        $message = $template 
            ? $template->render([
                'customer_name' => $cart->customer_name ?? 'عميلنا العزيز',
                'store_name' => $store->name_ar ?? $store->name,
                'cart_url' => $cart->recovery_url,
                'cart_total' => number_format($cart->subtotal, 0) . ' د.ج',
            ])
            : "مرحباً {$cart->customer_name}! 🛒\nلاحظنا أنك تركت منتجات في سلتك بقيمة " . number_format($cart->subtotal, 0) . " د.ج\nأكمل طلبك الآن: {$cart->recovery_url}\n{$store->name}";

        // TODO: Send via WhatsApp API (Green API / WhatsApp Business)
        // This will be implemented when integrations are connected

        $cart->markReminderSent();

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال التذكير بنجاح',
            'data' => [
                'reminder_count' => $cart->reminder_count,
                'sent_to' => $cart->customer_phone,
            ],
        ]);
    }

    /**
     * Mark abandoned cart as ignored
     * @route PUT /api/v1/dashboard/abandoned-carts/{id}/ignore
     */
    public function ignore(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $cart = AbandonedCart::where('store_id', $store->id)->where('id', $id)->first();

        if (!$cart) {
            return response()->json(['success' => false, 'message' => 'السلة غير موجودة'], 404);
        }

        $cart->update(['status' => 'ignored']);

        return response()->json([
            'success' => true,
            'message' => 'تم تجاهل السلة',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // PRIVATE HELPERS
    // ═══════════════════════════════════════════════════════════════

    private function getReminderBlockReason(AbandonedCart $cart): string
    {
        if ($cart->status !== 'active') return 'السلة ليست نشطة';
        if (!$cart->customer_phone) return 'لا يوجد رقم هاتف';
        if ($cart->reminder_count >= 3) return 'تم إرسال الحد الأقصى من التذكيرات';
        if ($cart->created_at->lt(now()->subDays(7))) return 'السلة قديمة جداً';
        if ($cart->reminder_sent_at && $cart->reminder_sent_at->gt(now()->subHours(24))) return 'تم إرسال تذكير خلال 24 ساعة';
        return 'سبب غير معروف';
    }
}
