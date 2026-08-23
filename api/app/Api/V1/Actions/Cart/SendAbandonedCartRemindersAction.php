<?php

namespace App\Api\V1\Actions\Cart;

use App\Models\Cart;
use App\Notifications\AbandonedCartReminderNotification;

class SendAbandonedCartRemindersAction
{
    public function execute(): int
    {
        $threshold = now()->subHours((int) config('cart.abandoned_reminder_hours'));

        // Checked-out carts don't need a separate "status" check: PlaceOrderAction/
        // PlaceQuickOrderAction clear cart items on order placement, so whereHas('items')
        // already excludes them along with carts that were simply never filled.
        $carts = Cart::query()
            ->whereNotNull('user_id')
            ->whereNull('reminder_sent_at')
            ->where('updated_at', '<=', $threshold)
            ->whereHas('items')
            ->with(['user', 'items.variant.product'])
            ->get();

        foreach ($carts as $cart) {
            $cart->update(['reminder_sent_at' => now()]);
            $cart->user->notify(new AbandonedCartReminderNotification($cart));
        }

        return $carts->count();
    }
}
