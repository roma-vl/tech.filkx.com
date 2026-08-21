<?php

return [
    // How long a cart with items must sit untouched before it's considered abandoned
    // and a reminder email is sent (one reminder per cart, see Cart::reminder_sent_at).
    'abandoned_reminder_hours' => env('CART_ABANDONED_REMINDER_HOURS', 4),
];
