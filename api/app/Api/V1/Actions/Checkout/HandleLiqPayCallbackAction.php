<?php

namespace App\Api\V1\Actions\Checkout;

use App\Models\Order;
use App\Services\Payment\LiqPayService;
use Illuminate\Support\Facades\Log;

class HandleLiqPayCallbackAction
{
    private const PAID_STATUSES = ['success', 'sandbox'];

    private const FAILED_STATUSES = ['failure', 'error'];

    private const REFUNDED_STATUSES = ['reversed'];

    public function __construct(
        private readonly LiqPayService $liqPay
    ) {}

    public function execute(string $data, string $signature): void
    {
        if (! $this->liqPay->verifySignature($data, $signature)) {
            Log::warning('LiqPay callback: invalid signature');

            return;
        }

        $payload = $this->liqPay->decodeCallbackData($data);
        $orderNumber = $payload['order_id'] ?? null;
        $status = $payload['status'] ?? null;

        if (! $orderNumber || ! $status) {
            Log::warning('LiqPay callback: missing order_id or status', ['payload' => $payload]);

            return;
        }

        $order = Order::where('order_number', $orderNumber)->first();

        if (! $order) {
            Log::warning('LiqPay callback: order not found', ['order_number' => $orderNumber]);

            return;
        }

        // Already processed - LiqPay retries callbacks until it gets a 200, don't reprocess.
        if ($order->payment_status === 'paid') {
            return;
        }

        if (in_array($status, self::PAID_STATUSES, true)) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'processing',
                'payment_reference' => $payload['payment_id'] ?? null,
                'paid_at' => now(),
            ]);
        } elseif (in_array($status, self::FAILED_STATUSES, true)) {
            $order->update([
                'payment_status' => 'failed',
                'payment_reference' => $payload['payment_id'] ?? null,
            ]);
        } elseif (in_array($status, self::REFUNDED_STATUSES, true)) {
            $order->update([
                'payment_status' => 'refunded',
                'status' => 'refunded',
            ]);
        }
        // Any other status (wait_accept, processing, 3ds_verify, ...) is an intermediate
        // state - LiqPay will send a follow-up callback once it resolves.
    }
}
