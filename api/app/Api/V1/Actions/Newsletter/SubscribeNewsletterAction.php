<?php

namespace App\Api\V1\Actions\Newsletter;

use App\Models\NewsletterSubscriber;

class SubscribeNewsletterAction
{
    public function execute(string $email): NewsletterSubscriber
    {
        return NewsletterSubscriber::firstOrCreate(['email' => $email]);
    }
}
