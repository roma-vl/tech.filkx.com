<?php

namespace App\Api\Admin\Actions\Order;

use App\Api\Admin\Dto\UpdateOrderStatusDto;
use App\Api\V1\Exceptions\OrderNotFoundException;
use App\Api\V1\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;

class UpdateAdminOrderStatusAction
{
    public function __construct(
        protected OrderRepositoryInterface $orderRepository
    ) {}

    public function execute(int $id, UpdateOrderStatusDto $dto): Order
    {
        $order = $this->orderRepository->findWithItems($id);

        if (! $order) {
            throw new OrderNotFoundException;
        }

        return DB::transaction(function () use ($order, $dto) {
            if ($dto->carrier !== null) {
                $order->carrier = $dto->carrier;
            }
            if ($dto->trackingNumber !== null) {
                $order->tracking_number = $dto->trackingNumber;
            }

            $oldStatus = $order->status;
            $newStatus = $dto->status;

            if ($oldStatus !== $newStatus) {
                foreach ($order->items as $item) {
                    if (! $item->variant) {
                        continue;
                    }
                    $stock = Stock::where('variant_id', $item->variant_id)->first();
                    if (! $stock) {
                        continue;
                    }

                    // Case A: transitioning from pending_payment to paid/processing (conversion)
                    if ($oldStatus === 'pending_payment' && in_array($newStatus, ['paid', 'processing', 'packed', 'shipped', 'delivered', 'completed'])) {
                        $stock->decrement('reserved', $item->quantity);
                        $stock->decrement('quantity', $item->quantity);
                    }
                    // Case B: transitioning from pending_payment to cancelled/refunded (release reservation)
                    elseif ($oldStatus === 'pending_payment' && in_array($newStatus, ['cancelled', 'refunded'])) {
                        $stock->decrement('reserved', $item->quantity);
                    }
                    // Case C: transitioning from paid/processing/completed to cancelled/refunded (return stock)
                    elseif (in_array($oldStatus, ['paid', 'processing', 'packed', 'shipped', 'delivered', 'completed']) && in_array($newStatus, ['cancelled', 'refunded'])) {
                        $stock->increment('quantity', $item->quantity);
                    }
                    // Case D: transitioning from cancelled/refunded back to paid/processing (re-deduct)
                    elseif (in_array($oldStatus, ['cancelled', 'refunded']) && in_array($newStatus, ['paid', 'processing', 'packed', 'shipped', 'delivered', 'completed'])) {
                        $stock->decrement('quantity', $item->quantity);
                    }
                }

                $order->status = $newStatus;

                // Auto-sync payment status
                if (in_array($newStatus, ['paid', 'processing', 'packed', 'shipped', 'delivered', 'completed'])) {
                    $order->payment_status = 'paid';
                } elseif ($newStatus === 'refunded') {
                    $order->payment_status = 'refunded';
                } elseif ($newStatus === 'cancelled') {
                    $order->payment_status = 'failed';
                }
            }

            $order->save();

            return $order->load('items');
        });
    }
}
