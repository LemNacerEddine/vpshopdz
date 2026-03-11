<?php

namespace App\Services;

use App\Models\Store;
use App\Models\NotificationTemplate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification via specified channel
     */
    public function send(Store $store, string $channel, string $to, string $message): bool
    {
        return match ($channel) {
            'whatsapp' => $this->sendWhatsApp($store, $to, $message),
            'telegram' => $this->sendTelegram($store, $to, $message),
            'sms' => $this->sendSMS($store, $to, $message),
            default => false,
        };
    }

    /**
     * Send WhatsApp message
     */
    public function sendWhatsApp(Store $store, string $phone, string $message): bool
    {
        $settings = $store->settings['notifications'] ?? [];
        $apiUrl = $settings['whatsapp_api_url'] ?? config('platform.whatsapp_api_url');
        $apiToken = $settings['whatsapp_api_token'] ?? config('platform.whatsapp_api_token');

        if (!$apiUrl || !$apiToken) {
            Log::warning("WhatsApp API not configured for store: {$store->id}");
            return false;
        }

        try {
            $response = Http::withToken($apiToken)
                ->post($apiUrl, [
                    'phone' => $this->formatPhone($phone),
                    'message' => $message,
                ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("WhatsApp send failed: {$e->getMessage()}", [
                'store_id' => $store->id,
                'phone' => $phone,
            ]);
            return false;
        }
    }

    /**
     * Send Telegram message
     */
    public function sendTelegram(Store $store, string $chatId, string $message): bool
    {
        $settings = $store->settings['notifications'] ?? [];
        $botToken = $settings['telegram_bot_token'] ?? config('platform.telegram_bot_token');

        if (!$botToken) {
            Log::warning("Telegram bot not configured for store: {$store->id}");
            return false;
        }

        try {
            $response = Http::post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Telegram send failed: {$e->getMessage()}", [
                'store_id' => $store->id,
                'chat_id' => $chatId,
            ]);
            return false;
        }
    }

    /**
     * Send SMS (placeholder)
     */
    public function sendSMS(Store $store, string $phone, string $message): bool
    {
        // TODO: Implement SMS provider integration
        Log::info("SMS send requested (not implemented)", [
            'store_id' => $store->id,
            'phone' => $phone,
            'message' => $message,
        ]);
        return false;
    }

    /**
     * Process template with variables
     */
    public function processTemplate(NotificationTemplate $template, array $variables): string
    {
        $content = $template->content;

        foreach ($variables as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }

        return $content;
    }

    /**
     * Send order notification
     */
    public function sendOrderNotification(Store $store, $order, string $event): void
    {
        $settings = $store->settings['notifications'] ?? [];

        // Find template for this event
        $template = NotificationTemplate::where('store_id', $store->id)
            ->where('event', $event)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            return;
        }

        $variables = [
            'order_number' => $order->order_number,
            'customer_name' => $order->customer_name,
            'customer_phone' => $order->customer_phone,
            'total' => number_format($order->total, 2),
            'status' => $order->status,
            'store_name' => $store->name,
            'tracking_url' => url("/api/v1/store/{$store->slug}/orders/{$order->tracking_number}"),
        ];

        $message = $this->processTemplate($template, $variables);

        // Send to merchant
        if ($template->send_to_merchant && !empty($settings['merchant_phone'])) {
            foreach ($template->channels as $channel) {
                $this->send($store, $channel, $settings['merchant_phone'], $message);
            }
        }

        // Send to customer
        if ($template->send_to_customer && $order->customer_phone) {
            foreach ($template->channels as $channel) {
                $this->send($store, $channel, $order->customer_phone, $message);
            }
        }
    }

    /**
     * Send abandoned cart recovery
     */
    public function sendAbandonedCartRecovery(Store $store, $cart): void
    {
        $template = NotificationTemplate::where('store_id', $store->id)
            ->where('event', 'abandoned_cart')
            ->where('is_active', true)
            ->first();

        if (!$template || !$cart->customer_phone) {
            return;
        }

        $variables = [
            'customer_name' => $cart->customer_name ?? 'عميل',
            'cart_total' => number_format($cart->total, 2),
            'store_name' => $store->name,
            'recovery_url' => url("/store/{$store->slug}?recover_cart={$cart->id}"),
        ];

        $message = $this->processTemplate($template, $variables);

        foreach ($template->channels as $channel) {
            $this->send($store, $channel, $cart->customer_phone, $message);
        }
    }

    /**
     * Format phone number for Algeria
     */
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($phone, '0')) {
            $phone = '213' . substr($phone, 1);
        }

        if (!str_starts_with($phone, '213')) {
            $phone = '213' . $phone;
        }

        return $phone;
    }
}
