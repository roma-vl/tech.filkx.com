<?php

namespace Tests\Unit\Actions\Admin\Accounting;

use App\Api\Admin\Actions\Accounting\GetAccountingStatsAction;
use App\Api\V1\Repositories\OrderRepositoryInterface;
use Tests\TestCase;

class GetAccountingStatsActionTest extends TestCase
{
    public function test_execute_delegates_to_the_repository(): void
    {
        $stats = ['totalRevenueMinor' => 100, 'totalRefundsMinor' => 0, 'netRevenueMinor' => 100];

        $this->mock(OrderRepositoryInterface::class, function ($mock) use ($stats) {
            $mock->shouldReceive('getAccountingStats')->once()->andReturn($stats);
        });

        $result = app(GetAccountingStatsAction::class)->execute();

        $this->assertSame($stats, $result);
    }
}
