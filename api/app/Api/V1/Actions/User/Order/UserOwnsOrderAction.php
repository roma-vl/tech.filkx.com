<?php

namespace App\Api\V1\Actions\User\Order;

use App\Models\Order;
use App\Models\User;

class UserOwnsOrderAction
{
    /**
     * An order belongs to a user either by account (`user_id`) or, for guest
     * checkouts later claimed by a logged-in email, by matching customer email.
     */
    public function execute(User $user, Order $order): bool
    {
        return $order->user_id === $user->id || $order->customer_email === $user->email;
    }
}
