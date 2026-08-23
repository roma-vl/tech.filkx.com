<?php

namespace Tests\Unit\Actions\Admin\Accounting;

use App\Api\Admin\Actions\Accounting\GetInvoicesAction;
use App\Api\Admin\Dto\InvoiceFilterDto;
use App\Api\V1\Repositories\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class GetInvoicesActionTest extends TestCase
{
    public function test_execute_delegates_to_the_repository_with_the_dtos_filters(): void
    {
        $dto = new InvoiceFilterDto(search: 'FKX', status: 'paid');
        $paginator = $this->createMock(LengthAwarePaginator::class);

        $this->mock(OrderRepositoryInterface::class, function ($mock) use ($dto, $paginator) {
            $mock->shouldReceive('paginateInvoices')->once()->with($dto->toArray(), 25)->andReturn($paginator);
        });

        $result = app(GetInvoicesAction::class)->execute($dto, 25);

        $this->assertSame($paginator, $result);
    }
}
