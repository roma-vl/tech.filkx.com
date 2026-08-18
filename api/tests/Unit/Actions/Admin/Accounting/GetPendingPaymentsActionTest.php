<?php

namespace Tests\Unit\Actions\Admin\Accounting;

use App\Api\Admin\Actions\Accounting\GetPendingPaymentsAction;
use App\Api\V1\Repositories\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class GetPendingPaymentsActionTest extends TestCase
{
    public function test_execute_delegates_to_the_repository_with_the_given_per_page(): void
    {
        $paginator = $this->createMock(LengthAwarePaginator::class);

        $this->mock(OrderRepositoryInterface::class, function ($mock) use ($paginator) {
            $mock->shouldReceive('paginatePendingPayments')->once()->with(10)->andReturn($paginator);
        });

        $result = app(GetPendingPaymentsAction::class)->execute(10);

        $this->assertSame($paginator, $result);
    }
}
