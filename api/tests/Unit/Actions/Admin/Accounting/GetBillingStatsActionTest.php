<?php

namespace Tests\Unit\Actions\Admin\Accounting;

use App\Api\Admin\Actions\Accounting\GetBillingStatsAction;
use App\Api\V1\Repositories\OrderRepositoryInterface;
use Tests\TestCase;

class GetBillingStatsActionTest extends TestCase
{
    public function test_execute_delegates_to_the_repository(): void
    {
        $stats = ['revenueMinor' => 100, 'activeSubscriptions' => 0, 'pendingPaymentsCount' => 2];

        $this->mock(OrderRepositoryInterface::class, function ($mock) use ($stats) {
            $mock->shouldReceive('getBillingStats')->once()->andReturn($stats);
        });

        $result = app(GetBillingStatsAction::class)->execute();

        $this->assertSame($stats, $result);
    }
}
