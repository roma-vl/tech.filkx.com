<?php

namespace App\Api\Admin\Actions;

use Illuminate\Support\Collection;

class ListSubscriptionPlanNamesAction
{
    /**
     * Get a unique list of subscription plan names.
     */
    public function execute(): Collection
    {
        return collect(['Regular Client', 'VIP Client', 'Staff']);
    }
}
