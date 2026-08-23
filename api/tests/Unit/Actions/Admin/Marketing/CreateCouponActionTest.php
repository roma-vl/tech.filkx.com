<?php

namespace Tests\Unit\Actions\Admin\Marketing;

use App\Api\Admin\Actions\Marketing\CreateCouponAction;
use App\Api\Admin\Dto\CouponDto;
use App\Api\V1\Repositories\CouponRepositoryInterface;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateCouponActionTest extends TestCase
{
    use RefreshDatabase;

    private function makeDto(array $categoryIds = [], array $productIds = []): CouponDto
    {
        return new CouponDto(
            code: 'SAVE10',
            type: 'percent',
            amount: 10,
            usageLimit: null,
            expiresAt: null,
            isActive: true,
            categoryIds: $categoryIds,
            productIds: $productIds
        );
    }

    public function test_execute_creates_the_coupon_via_the_repository(): void
    {
        $dto = $this->makeDto();
        $coupon = Coupon::create($dto->toArray());

        $this->mock(CouponRepositoryInterface::class, function ($mock) use ($dto, $coupon) {
            $mock->shouldReceive('create')->once()->with($dto->toArray())->andReturn($coupon);
        });

        $result = app(CreateCouponAction::class)->execute($dto);

        $this->assertSame($coupon->id, $result->id);
    }

    public function test_execute_syncs_the_given_categories_and_products(): void
    {
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'Кат', 'en' => 'Cat']]);
        $product = Product::create([
            'slug' => 'prod-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
        $dto = $this->makeDto([$category->id], [$product->id]);
        $coupon = Coupon::create($dto->toArray());

        $this->mock(CouponRepositoryInterface::class, function ($mock) use ($coupon) {
            $mock->shouldReceive('create')->once()->andReturn($coupon);
        });

        $result = app(CreateCouponAction::class)->execute($dto);

        $this->assertTrue($result->categories()->where('categories.id', $category->id)->exists());
        $this->assertTrue($result->products()->where('products.id', $product->id)->exists());
    }
}
