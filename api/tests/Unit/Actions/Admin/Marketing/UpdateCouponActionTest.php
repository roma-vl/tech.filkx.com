<?php

namespace Tests\Unit\Actions\Admin\Marketing;

use App\Api\Admin\Actions\Marketing\UpdateCouponAction;
use App\Api\Admin\Dto\CouponDto;
use App\Api\V1\Exceptions\CouponNotFoundException;
use App\Api\V1\Repositories\CouponRepositoryInterface;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateCouponActionTest extends TestCase
{
    use RefreshDatabase;

    private function makeDto(array $categoryIds = [], array $productIds = []): CouponDto
    {
        return new CouponDto(
            code: 'SAVE20',
            type: 'fixed',
            amount: 20,
            usageLimit: 10,
            expiresAt: null,
            isActive: true,
            categoryIds: $categoryIds,
            productIds: $productIds
        );
    }

    public function test_execute_updates_the_coupon_via_the_repository(): void
    {
        $coupon = Coupon::create(['code' => 'OLD', 'type' => 'percent', 'amount' => 5, 'is_active' => true]);
        $dto = $this->makeDto();

        $this->mock(CouponRepositoryInterface::class, function ($mock) use ($coupon, $dto) {
            $mock->shouldReceive('find')->once()->with($coupon->id)->andReturn($coupon);
            $mock->shouldReceive('update')->once()->with($coupon, $dto->toArray())->andReturnUsing(function ($coupon, $data) {
                $coupon->update($data);

                return $coupon;
            });
        });

        $result = app(UpdateCouponAction::class)->execute($coupon->id, $dto);

        $this->assertSame('SAVE20', $result->code);
    }

    public function test_execute_syncs_categories_and_products(): void
    {
        $coupon = Coupon::create(['code' => 'OLD', 'type' => 'percent', 'amount' => 5, 'is_active' => true]);
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'Кат', 'en' => 'Cat']]);
        $product = Product::create([
            'slug' => 'prod-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
        $dto = $this->makeDto([$category->id], [$product->id]);

        $this->mock(CouponRepositoryInterface::class, function ($mock) use ($coupon) {
            $mock->shouldReceive('find')->once()->andReturn($coupon);
            $mock->shouldReceive('update')->once()->andReturn($coupon);
        });

        $result = app(UpdateCouponAction::class)->execute($coupon->id, $dto);

        $this->assertTrue($result->categories()->where('categories.id', $category->id)->exists());
        $this->assertTrue($result->products()->where('products.id', $product->id)->exists());
    }

    public function test_execute_throws_when_the_coupon_does_not_exist(): void
    {
        $this->mock(CouponRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('find')->once()->with(999)->andReturn(null);
            $mock->shouldNotReceive('update');
        });

        $this->expectException(CouponNotFoundException::class);

        app(UpdateCouponAction::class)->execute(999, $this->makeDto());
    }
}
