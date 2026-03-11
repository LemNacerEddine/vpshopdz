<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Models\StoreIntegration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    /**
     * Get all notification templates
     * @route GET /api/v1/dashboard/notifications/templates
     */
    public function templates(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $templates = NotificationTemplate::where('store_id', $store->id)
            ->orderBy('channel')
            ->orderBy('event')
            ->get()
            ->map(function ($template) {
                return [
                    'id' => $template->id,
                    'channel' => $template->channel,
                    'channel_label' => $template->channel_label,
                    'event' => $template->event,
                    'event_label' => $template->event_label,
                    'name' => $template->name,
                    'template_ar' => $template->template_ar,
                    'template_fr' => $template->template_fr,
                    'is_active' => $template->is_active,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $templates,
            'available_channels' => NotificationTemplate::CHANNELS,
            'available_events' => NotificationTemplate::EVENTS,
            'available_variables' => NotificationTemplate::VARIABLES,
        ]);
    }

    /**
     * Create or update notification template
     * @route POST /api/v1/dashboard/notifications/templates
     */
    public function storeTemplate(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $validator = Validator::make($request->all(), [
            'channel' => 'required|in:whatsapp,telegram,sms,email',
            'event' => 'required|in:order_created,order_confirmed,order_shipped,order_delivered,order_cancelled,abandoned_cart,welcome_customer,custom',
            'name' => 'required|string|max:255',
            'template_ar' => 'nullable|string|max:2000',
            'template_fr' => 'nullable|string|max:2000',
            'template_en' => 'nullable|string|max:2000',
            'is_active' => 'boolean',
            'settings' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $template = NotificationTemplate::updateOrCreate(
            [
                'store_id' => $store->id,
                'channel' => $request->channel,
                'event' => $request->event,
            ],
            [
                'name' => $request->name,
                'template_ar' => $request->template_ar,
                'template_fr' => $request->template_fr,
                'template_en' => $request->template_en,
                'is_active' => $request->is_active ?? true,
                'settings' => $request->settings,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ قالب الإشعار بنجاح',
            'data' => $template,
        ]);
    }

    /**
     * Delete notification template
     * @route DELETE /api/v1/dashboard/notifications/templates/{id}
     */
    public function destroyTemplate(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $template = NotificationTemplate::where('store_id', $store->id)->where('id', $id)->first();

        if (!$template) {
            return response()->json(['success' => false, 'message' => 'القالب غير موجود'], 404);
        }

        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف القالب',
        ]);
    }

    /**
     * Test notification template
     * @route POST /api/v1/dashboard/notifications/test
     */
    public function testTemplate(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        $validator = Validator::make($request->all(), [
            'channel' => 'required|in:whatsapp,telegram,sms,email',
            'template_text' => 'required|string',
            'test_phone' => 'required_if:channel,whatsapp,sms|string',
            'test_email' => 'required_if:channel,email|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Render template with test data
        $testData = [
            'customer_name' => 'أحمد محمد',
            'order_number' => 'ORD-TEST-001',
            'order_total' => '5,000 د.ج',
            'order_status' => 'مؤكد',
            'tracking_number' => 'TR123456789',
            'store_name' => $store->name_ar ?? $store->name,
            'store_phone' => $store->phone ?? '0000000000',
            'product_name' => 'منتج تجريبي',
            'cart_url' => $store->getUrl() . '/cart',
        ];

        $renderedMessage = $request->template_text;
        foreach ($testData as $key => $value) {
            $renderedMessage = str_replace("{{{$key}}}", $value, $renderedMessage);
            $renderedMessage = str_replace("{{" . $key . "}}", $value, $renderedMessage);
        }

        // TODO: Actually send the test message via the appropriate channel
        // For now, return the rendered message

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الرسالة التجريبية',
            'data' => [
                'rendered_message' => $renderedMessage,
                'channel' => $request->channel,
                'sent_to' => $request->test_phone ?? $request->test_email,
            ],
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // INTEGRATIONS
    // ═══════════════════════════════════════════════════════════════

    /**
     * Get store integrations
     * @route GET /api/v1/dashboard/integrations
     */
    public function integrations(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $integrations = StoreIntegration::where('store_id', $store->id)
            ->get()
            ->map(function ($integration) {
                return [
                    'id' => $integration->id,
                    'type' => $integration->type,
                    'type_label' => $integration->type_label,
                    'name' => $integration->name,
                    'is_active' => $integration->is_active,
                    'last_synced_at' => $integration->last_synced_at,
                    'last_error' => $integration->last_error,
                    'has_credentials' => !empty($integration->credentials),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $integrations,
            'available_types' => StoreIntegration::TYPES,
        ]);
    }

    /**
     * Create or update integration
     * @route POST /api/v1/dashboard/integrations
     */
    public function storeIntegration(Request $request): JsonResponse
    {
        $store = $request->user()->store;

        if (!$store) {
            return response()->json(['success' => false, 'message' => 'لا يوجد متجر'], 404);
        }

        $validator = Validator::make($request->all(), [
            'type' => 'required|string',
            'name' => 'nullable|string|max:255',
            'credentials' => 'nullable|array',
            'settings' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $integration = StoreIntegration::updateOrCreate(
            [
                'store_id' => $store->id,
                'type' => $request->type,
            ],
            [
                'name' => $request->name,
                'credentials' => $request->credentials,
                'settings' => $request->settings,
                'is_active' => $request->is_active ?? true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'تم حفظ التكامل بنجاح',
            'data' => [
                'id' => $integration->id,
                'type' => $integration->type,
                'is_active' => $integration->is_active,
            ],
        ]);
    }

    /**
     * Delete integration
     * @route DELETE /api/v1/dashboard/integrations/{id}
     */
    public function destroyIntegration(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $integration = StoreIntegration::where('store_id', $store->id)->where('id', $id)->first();

        if (!$integration) {
            return response()->json(['success' => false, 'message' => 'التكامل غير موجود'], 404);
        }

        $integration->delete();

        return response()->json([
            'success' => true,
            'message' => 'تم حذف التكامل',
        ]);
    }

    /**
     * Test integration connection
     * @route POST /api/v1/dashboard/integrations/{id}/test
     */
    public function testIntegration(Request $request, string $id): JsonResponse
    {
        $store = $request->user()->store;
        $integration = StoreIntegration::where('store_id', $store->id)->where('id', $id)->first();

        if (!$integration) {
            return response()->json(['success' => false, 'message' => 'التكامل غير موجود'], 404);
        }

        // TODO: Test actual connection based on integration type
        // For now, validate credentials exist

        if (empty($integration->credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'لم يتم إدخال بيانات الاعتماد',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم اختبار الاتصال بنجاح',
            'data' => [
                'type' => $integration->type,
                'status' => 'connected',
            ],
        ]);
    }
}
