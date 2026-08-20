<?php

namespace Tests\Unit\Actions\Admin;

use App\Api\Admin\Actions\ListSubscriptionPlanNamesAction;
use Tests\TestCase;

class ListSubscriptionPlanNamesActionTest extends TestCase
{
    public function test_execute_returns_the_fixed_plan_names(): void
    {
        $result = app(ListSubscriptionPlanNamesAction::class)->execute();

        $this->assertSame(['Regular Client', 'VIP Client', 'Staff'], $result->all());
    }
}
