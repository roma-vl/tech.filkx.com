<?php

namespace App\Api\V1\Actions\User\Order;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class GetUserOrdersAction
{
    private const FALLBACK_ITEM_IMAGE = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=600&fit=crop';

    private const UKRAINIAN_MONTHS = [
        1 => 'Січ', 2 => 'Лют', 3 => 'Бер', 4 => 'Кві', 5 => 'Тра', 6 => 'Чер',
        7 => 'Лип', 8 => 'Сер', 9 => 'Вер', 10 => 'Жов', 11 => 'Лис', 12 => 'Гру',
    ];

    /** Order statuses whose tracking timeline is fully complete. */
    private const FINAL_STATUSES = ['delivered', 'completed'];

    /** Order statuses whose tracking timeline stopped early (no further steps). */
    private const TERMINATED_STATUSES = ['cancelled', 'refunded'];

    public function execute(User $user): Collection
    {
        $orders = Order::with(['items.variant.product'])
            ->where('user_id', $user->id)
            ->orWhere('customer_email', $user->email)
            ->orderBy('created_at', 'desc')
            ->get();

        return $orders->map(fn (Order $order) => $this->formatOrder($order));
    }

    private function formatOrder(Order $order): array
    {
        $mappedStatus = $this->mapOrderStatus($order->status, $order->updated_at);

        return [
            'id' => $order->order_number ?: (string) $order->id,
            'dbId' => $order->id,
            'date' => $this->formatUkrainianDate($order->created_at),
            'total' => (float) $order->total_price,
            'shipTo' => $order->customer_name ?: '',
            'status' => $mappedStatus['status'],
            'statusIcon' => $mappedStatus['statusIcon'],
            'statusClass' => $mappedStatus['statusClass'],
            'statusCode' => $mappedStatus['statusCode'],
            'trackingSteps' => $this->generateTrackingSteps($mappedStatus['statusCode'], $order->created_at, $order->updated_at),
            'shippingAddress' => [
                'recipient' => $order->customer_name ?: '',
                'street' => $order->shipping_address ?: '',
                'city' => $order->shipping_city ?: 'Київ',
                'state' => 'Київська обл.',
                'zip' => '01001',
                'country' => $order->shipping_country ?: 'Україна',
            ],
            'paymentMethod' => [
                'type' => $order->payment_method ?: 'Картка',
                'number' => '•••• 4242',
            ],
            'items' => $order->items->map(fn (OrderItem $item) => $this->formatOrderItem($item))->toArray(),
        ];
    }

    private function formatOrderItem(OrderItem $item): array
    {
        $variant = $item->variant;
        $images = $variant ? ($variant->dimensions['images'] ?? []) : [];
        $imageUrl = ! empty($images) ? ($images[0]['url'] ?? null) : self::FALLBACK_ITEM_IMAGE;

        return [
            'id' => $item->id,
            'slug' => $variant && $variant->product ? $variant->product->slug : '',
            'name' => $item->product_name,
            'price' => (float) $item->price,
            'image' => $imageUrl,
            'qty' => $item->quantity,
        ];
    }

    private function formatUkrainianDate(Carbon $date): string
    {
        $day = $date->format('d');
        $monthNum = (int) $date->format('m');
        $year = $date->format('Y');

        return "{$day} ".self::UKRAINIAN_MONTHS[$monthNum].", {$year}";
    }

    private function mapOrderStatus(string $dbStatus, Carbon $updatedAt): array
    {
        $formattedUpdateDate = $this->formatUkrainianDate($updatedAt);

        return match ($dbStatus) {
            'completed' => [
                'statusCode' => 'completed',
                'status' => "Виконано {$formattedUpdateDate}",
                'statusIcon' => 'task_alt',
                'statusClass' => 'text-zinc-500 dark:text-zinc-455 bg-zinc-50 dark:bg-zinc-850 border border-zinc-200 dark:border-zinc-700',
            ],
            'delivered' => [
                'statusCode' => 'delivered',
                'status' => "Доставлено {$formattedUpdateDate}",
                'statusIcon' => 'check_circle',
                'statusClass' => 'text-teal-500 bg-teal-550/10 border border-teal-550/20',
            ],
            'cancelled' => [
                'statusCode' => 'cancelled',
                'status' => "Скасовано {$formattedUpdateDate}",
                'statusIcon' => 'cancel',
                'statusClass' => 'text-rose-500 bg-rose-500/10 border border-rose-500/20',
            ],
            'refunded' => [
                'statusCode' => 'refunded',
                'status' => "Повернуто кошти {$formattedUpdateDate}",
                'statusIcon' => 'undo',
                'statusClass' => 'text-gray-500 bg-gray-500/10 border border-gray-500/20',
            ],
            'shipped' => [
                'statusCode' => 'shipped',
                'status' => 'Відправлено - в дорозі',
                'statusIcon' => 'local_shipping',
                'statusClass' => 'text-[#00a046] bg-emerald-500/10 border border-emerald-500/20',
            ],
            'pending_payment' => [
                'statusCode' => 'pending_payment',
                'status' => 'Очікує оплати',
                'statusIcon' => 'hourglass_empty',
                'statusClass' => 'text-amber-500 bg-amber-500/10 border border-amber-500/20',
            ],
            'paid' => [
                'statusCode' => 'paid',
                'status' => 'Оплачено',
                'statusIcon' => 'payments',
                'statusClass' => 'text-emerald-500 bg-emerald-500/10 border border-emerald-500/20',
            ],
            'processing' => [
                'statusCode' => 'processing',
                'status' => 'Обробляється',
                'statusIcon' => 'hourglass_empty',
                'statusClass' => 'text-blue-550 bg-blue-550/10 border border-blue-550/20',
            ],
            'packed' => [
                'statusCode' => 'packed',
                'status' => 'Запаковано',
                'statusIcon' => 'inventory_2',
                'statusClass' => 'text-cyan-500 bg-cyan-500/10 border border-cyan-500/20',
            ],
            default => [
                'statusCode' => 'pending',
                'status' => 'В обробці',
                'statusIcon' => 'hourglass_empty',
                'statusClass' => 'text-amber-505 bg-amber-500/10 border border-amber-500/20',
            ],
        };
    }

    private function generateTrackingSteps(string $statusCode, Carbon $createdAt, Carbon $updatedAt): array
    {
        $createdStr = $createdAt->format('d.m.Y H:i');
        $updatedStr = $updatedAt->format('d.m.Y H:i');

        if (in_array($statusCode, self::TERMINATED_STATUSES, true)) {
            $name = $statusCode === 'refunded' ? 'Повернуто кошти' : 'Скасовано';

            return [
                ['name' => 'Замовлення створено', 'date' => $createdStr, 'done' => true],
                ['name' => $name, 'date' => $updatedStr, 'done' => true],
            ];
        }

        if (in_array($statusCode, self::FINAL_STATUSES, true)) {
            $name = $statusCode === 'completed' ? 'Виконано' : 'Доставлено';

            return [
                ['name' => 'Замовлення створено', 'date' => $createdStr, 'done' => true],
                ['name' => 'Обробка та комплектування', 'date' => $createdStr, 'done' => true],
                ['name' => 'Передано кур\'єру', 'date' => $updatedStr, 'done' => true],
                ['name' => 'Доставка отримувачу', 'date' => $updatedStr, 'done' => true],
                ['name' => $name, 'date' => $updatedStr, 'done' => true],
            ];
        }

        if ($statusCode === 'shipped') {
            return [
                ['name' => 'Замовлення створено', 'date' => $createdStr, 'done' => true],
                ['name' => 'Обробка та комплектування', 'date' => $createdStr, 'done' => true],
                ['name' => 'Передано кур\'єру', 'date' => $updatedStr, 'done' => true],
                ['name' => 'Доставка отримувачу', 'date' => 'Очікується найближчим часом', 'done' => false],
                ['name' => 'Доставлено', 'date' => 'Очікується', 'done' => false],
            ];
        }

        $stageText = match ($statusCode) {
            'pending_payment' => 'Очікує оплати',
            'paid' => 'Оплачено',
            'packed' => 'Запаковано',
            default => 'В процесі',
        };

        return [
            ['name' => 'Замовлення створено', 'date' => $createdStr, 'done' => true],
            ['name' => 'Обробка та комплектування', 'date' => $stageText, 'done' => $statusCode !== 'pending_payment'],
            ['name' => 'Передано кур\'єру', 'date' => 'Очікується', 'done' => false],
            ['name' => 'Доставлено', 'date' => 'Очікується', 'done' => false],
        ];
    }
}
