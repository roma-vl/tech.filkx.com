<?php

namespace Tests\Unit\Actions\Admin\Marketing;

use App\Api\Admin\Actions\Marketing\DeleteCouponAction;
use App\Api\V1\Exceptions\CouponNotFoundException;
use App\Api\V1\Repositories\CouponRepositoryInterface;
use App\Models\Coupon;
use Tests\TestCase;

class DeleteCouponActionTest extends TestCase
{
    public function test_execute_deletes_the_coupon_when_found(): void
    {
        $coupon = new Coupon(['code' => 'SAVE10']);

        $this->mock(CouponRepositoryInterface::class, function ($mock) use ($coupon) {
            $mock->shouldReceive('find')->once()->with(1)->andReturn($coupon);
            $mock->shouldReceive('delete')->once()->with($coupon)->andReturn(true);
        });

        app(DeleteCouponAction::class)->execute(1);

        $this->addToAssertionCount(1);
    }

    public function test_execute_throws_when_the_coupon_does_not_exist(): void
    {
        $this->mock(CouponRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('find')->once()->with(999)->andReturn(null);
            $mock->shouldNotReceive('delete');
        });

        $this->expectException(CouponNotFoundException::class);

        app(DeleteCouponAction::class)->execute(999);
    }
}
