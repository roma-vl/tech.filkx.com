<?php

namespace App\Api\V1\Actions\User\Order;

use App\Api\Admin\Actions\Order\UpdateAdminOrderStatusAction;
use App\Api\Admin\Dto\UpdateOrderStatusDto;
use App\Api\V1\Exceptions\OrderAccessDeniedException;
use App\Api\V1\Exceptions\OrderAlreadyCancelledException;
use App\Api\V1\Exceptions\OrderNotCancellableException;
use App\Api\V1\Exceptions\OrderNotFoundException;
use App\Models\Order;
use App\Models\User;

class CancelUserOrderAction
{
    /** Statuses past which a customer can no longer self-cancel an order. */
    private const NON_CANCELLABLE_STATUSES = ['shipped', 'delivered', 'completed', 'refunded'];

    public function __construct(
        private readonly UserOwnsOrderAction $userOwnsOrderAction,
        private readonly UpdateAdminOrderStatusAction $updateAdminOrderStatusAction,
    ) {}

    /**
     * @throws OrderNotFoundException
     * @throws OrderAccessDeniedException
     * @throws OrderNotCancellableException
     * @throws OrderAlreadyCancelledException
     */
    public function execute(User $user, int $orderId): Order
    {
        $order = Order::find($orderId);

        if (! $order) {
            throw new OrderNotFoundException('Замовлення не знайдено');
        }

        if (! $this->userOwnsOrderAction->execute($user, $order)) {
            throw new OrderAccessDeniedException('У вас немає доступу до цього замовлення');
        }

        if (in_array($order->status, self::NON_CANCELLABLE_STATUSES, true)) {
            throw new OrderNotCancellableException('Це замовлення вже відправлено або виконано і його не можна скасувати.');
        }

        if ($order->status === 'cancelled') {
            throw new OrderAlreadyCancelledException('Це замовлення вже скасовано.');
        }

        return $this->updateAdminOrderStatusAction->execute($orderId, new UpdateOrderStatusDto(
            status: 'cancelled',
            carrier: null,
            trackingNumber: null,
        ));
    }
}
