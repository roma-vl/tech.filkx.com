<?php

namespace Tests\Unit\Actions\Admin\Marketing;

use App\Api\Admin\Actions\Marketing\ListPromotionsAction;
use App\Api\Admin\Dto\MarketingFilterDto;
use App\Api\V1\Repositories\PromotionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Tests\TestCase;

class ListPromotionsActionTest extends TestCase
{
    public function test_execute_delegates_to_the_repository_with_the_dtos_filters(): void
    {
        $dto = new MarketingFilterDto(search: null, status: 'expired', sortBy: 'amount', sortDir: 'asc');
        $paginator = $this->createMock(LengthAwarePaginator::class);

        $this->mock(PromotionRepositoryInterface::class, function ($mock) use ($dto, $paginator) {
            $mock->shouldReceive('paginate')->once()->with($dto->toArray(), 20)->andReturn($paginator);
        });

        $result = app(ListPromotionsAction::class)->execute($dto, 20);

        $this->assertSame($paginator, $result);
    }
}
