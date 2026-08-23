<?php

namespace App\Api\V1\Actions\Checkout;

use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Models\Order;
use App\Services\Payment\LiqPayService;

class InitiateLiqPayPaymentAction
{
    public function __construct(
        private readonly LiqPayService $liqPay
    ) {}

    public function execute(Order $order): array
    {
        if (! $this->liqPay->isConfigured()) {
            throw new CheckoutValidationException('Онлайн-оплата тимчасово недоступна. Оберіть інший спосіб оплати.');
        }

        if ($order->payment_status === 'paid') {
            throw new CheckoutValidationException('Це замовлення вже оплачено');
        }

        $frontendUrl = rtrim(config('app.frontend_url'), '/');
        $resultUrl = $frontendUrl.'/cart?payment=liqpay&order='.$order->order_number;
        $serverUrl = rtrim(config('app.url'), '/').'/api/v1/payments/liqpay/callback';

        return $this->liqPay->buildCheckoutPayload($order, $resultUrl, $serverUrl);
    }
}
