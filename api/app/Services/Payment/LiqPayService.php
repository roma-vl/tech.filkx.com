<?php

namespace App\Services\Payment;

use App\Models\Order;

class LiqPayService
{
    public const CHECKOUT_URL = 'https://www.liqpay.ua/api/3/checkout';

    public function isConfigured(): bool
    {
        return filled(config('services.liqpay.public_key')) && filled(config('services.liqpay.private_key'));
    }

    /**
     * Build the signed payload for LiqPay's hosted checkout form.
     * The caller submits `data` + `signature` as a POST form to self::CHECKOUT_URL.
     */
    public function buildCheckoutPayload(Order $order, string $resultUrl, string $serverUrl): array
    {
        $params = [
            'version' => 3,
            'public_key' => config('services.liqpay.public_key'),
            'action' => 'pay',
            'amount' => (float) $order->total_price,
            'currency' => 'UAH',
            'description' => "Оплата замовлення {$order->order_number}",
            'order_id' => $order->order_number,
            'result_url' => $resultUrl,
            'server_url' => $serverUrl,
            'language' => 'uk',
        ];

        if (config('services.liqpay.sandbox')) {
            $params['sandbox'] = 1;
        }

        $data = base64_encode(json_encode($params, JSON_UNESCAPED_UNICODE));

        return [
            'data' => $data,
            'signature' => $this->sign($data),
            'checkoutUrl' => self::CHECKOUT_URL,
        ];
    }

    public function verifySignature(string $data, string $signature): bool
    {
        return hash_equals($this->sign($data), $signature);
    }

    public function decodeCallbackData(string $data): array
    {
        return json_decode(base64_decode($data), true) ?? [];
    }

    private function sign(string $data): string
    {
        $privateKey = config('services.liqpay.private_key');

        return base64_encode(sha1($privateKey.$data.$privateKey, true));
    }
}
