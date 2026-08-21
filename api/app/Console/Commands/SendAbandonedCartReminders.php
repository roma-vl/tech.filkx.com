<?php

namespace App\Console\Commands;

use App\Api\V1\Actions\Cart\SendAbandonedCartRemindersAction;
use Illuminate\Console\Command;

class SendAbandonedCartReminders extends Command
{
    protected $signature = 'carts:send-abandoned-reminders';

    protected $description = 'Send a one-time reminder email for carts abandoned past the configured threshold';

    public function handle(SendAbandonedCartRemindersAction $action): int
    {
        $sent = $action->execute();

        $this->info("Abandoned cart reminders queued: {$sent}");

        return self::SUCCESS;
    }
}
