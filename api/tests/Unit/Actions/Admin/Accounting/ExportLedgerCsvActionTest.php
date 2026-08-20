<?php

namespace Tests\Unit\Actions\Admin\Accounting;

use App\Api\Admin\Actions\Accounting\ExportLedgerCsvAction;
use App\Api\V1\Repositories\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class ExportLedgerCsvActionTest extends TestCase
{
    public function test_execute_delegates_to_the_repository(): void
    {
        $orders = new Collection;

        $this->mock(OrderRepositoryInterface::class, function ($mock) use ($orders) {
            $mock->shouldReceive('getCompletedAndCancelledOrders')->once()->andReturn($orders);
        });

        $result = app(ExportLedgerCsvAction::class)->execute();

        $this->assertSame($orders, $result);
    }
}
