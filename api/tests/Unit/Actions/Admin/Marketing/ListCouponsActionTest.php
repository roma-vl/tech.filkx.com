<?php

namespace Tests\Unit\Actions\Admin\Marketing;

use App\Api\Admin\Actions\Marketing\ListCouponsAction;
use App\Api\Admin\Dto\MarketingFilterDto;
use App\Api\V1\Repositories\CouponRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ListCouponsActionTest extends TestCase
{
    public function test_execute_delegates_to_the_repository_with_the_dtos_filters(): void
    {
        $dto = new MarketingFilterDto(search: 'save', status: 'active', sortBy: 'created_at', sortDir: 'desc');
        $paginator = $this->createMock(LengthAwarePaginator::class);

        $this->mock(CouponRepositoryInterface::class, function ($mock) use ($dto, $paginator) {
            $mock->shouldReceive('paginate')->once()->with($dto->toArray(), 15)->andReturn($paginator);
        });

        $result = app(ListCouponsAction::class)->execute($dto, 15);

        $this->assertSame($paginator, $result);
    }
}
