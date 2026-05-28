<?php

namespace App\Api\Admin\Actions;

use App\Models\Billing\SubscriptionPlan;
use Illuminate\Support\Collection;

class ListSubscriptionPlanNamesAction
{
    /**
     * Get a unique list of subscription plan names.
     */
    public function execute(): Collection
    {
        return SubscriptionPlan::pluck('name')->unique()->values();
    }
}
