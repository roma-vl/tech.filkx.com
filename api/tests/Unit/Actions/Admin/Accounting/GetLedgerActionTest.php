<?php

namespace Tests\Unit\Actions\Admin\Accounting;

use App\Api\Admin\Actions\Accounting\GetLedgerAction;
use App\Api\Admin\Dto\LedgerFilterDto;
use App\Api\V1\Repositories\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class GetLedgerActionTest extends TestCase
{
    public function test_execute_delegates_to_the_repository_with_the_dtos_filters(): void
    {
        $dto = new LedgerFilterDto(userId: 5, type: 'charge', from: '2026-01-01', to: '2026-01-31');
        $paginator = $this->createMock(LengthAwarePaginator::class);

        $this->mock(OrderRepositoryInterface::class, function ($mock) use ($dto, $paginator) {
            $mock->shouldReceive('paginateLedger')->once()->with($dto->toArray(), 30)->andReturn($paginator);
        });

        $result = app(GetLedgerAction::class)->execute($dto, 30);

        $this->assertSame($paginator, $result);
    }
}
