<?php

namespace Tests\Unit\Actions\Admin\Marketing;

use App\Api\Admin\Actions\Marketing\DeletePromotionAction;
use App\Api\V1\Exceptions\PromotionNotFoundException;
use App\Api\V1\Repositories\PromotionRepositoryInterface;
use App\Models\Promotion;
use Tests\TestCase;

class DeletePromotionActionTest extends TestCase
{
    public function test_execute_deletes_the_promotion_when_found(): void
    {
        $promotion = new Promotion(['name' => 'Summer Sale']);

        $this->mock(PromotionRepositoryInterface::class, function ($mock) use ($promotion) {
            $mock->shouldReceive('find')->once()->with(1)->andReturn($promotion);
            $mock->shouldReceive('delete')->once()->with($promotion)->andReturn(true);
        });

        app(DeletePromotionAction::class)->execute(1);

        $this->addToAssertionCount(1);
    }

    public function test_execute_throws_when_the_promotion_does_not_exist(): void
    {
        $this->mock(PromotionRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('find')->once()->with(999)->andReturn(null);
            $mock->shouldNotReceive('delete');
        });

        $this->expectException(PromotionNotFoundException::class);

        app(DeletePromotionAction::class)->execute(999);
    }
}
