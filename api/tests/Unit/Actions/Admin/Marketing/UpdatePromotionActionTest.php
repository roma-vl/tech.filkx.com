<?php

namespace Tests\Unit\Actions\Admin\Marketing;

use App\Api\Admin\Actions\Marketing\UpdatePromotionAction;
use App\Api\Admin\Dto\PromotionDto;
use App\Api\V1\Exceptions\PromotionNotFoundException;
use App\Api\V1\Repositories\PromotionRepositoryInterface;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdatePromotionActionTest extends TestCase
{
    use RefreshDatabase;

    private function makeDto(array $categoryIds = [], array $productIds = []): PromotionDto
    {
        return new PromotionDto(
            name: 'Winter Sale',
            description: 'Updated',
            type: 'percent',
            amount: 25,
            startDate: null,
            endDate: null,
            isActive: false,
            categoryIds: $categoryIds,
            productIds: $productIds
        );
    }

    public function test_execute_updates_the_promotion_via_the_repository(): void
    {
        $promotion = Promotion::create(['name' => 'Old', 'type' => 'percent', 'amount' => 5, 'is_active' => true]);
        $dto = $this->makeDto();

        $this->mock(PromotionRepositoryInterface::class, function ($mock) use ($promotion, $dto) {
            $mock->shouldReceive('find')->once()->with($promotion->id)->andReturn($promotion);
            $mock->shouldReceive('update')->once()->with($promotion, $dto->toArray())->andReturnUsing(function ($promotion, $data) {
                $promotion->update($data);

                return $promotion;
            });
        });

        $result = app(UpdatePromotionAction::class)->execute($promotion->id, $dto);

        $this->assertSame('Winter Sale', $result->name);
    }

    public function test_execute_syncs_categories_and_products(): void
    {
        $promotion = Promotion::create(['name' => 'Old', 'type' => 'percent', 'amount' => 5, 'is_active' => true]);
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'Кат', 'en' => 'Cat']]);
        $product = Product::create([
            'slug' => 'prod-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
        $dto = $this->makeDto([$category->id], [$product->id]);

        $this->mock(PromotionRepositoryInterface::class, function ($mock) use ($promotion) {
            $mock->shouldReceive('find')->once()->andReturn($promotion);
            $mock->shouldReceive('update')->once()->andReturn($promotion);
        });

        $result = app(UpdatePromotionAction::class)->execute($promotion->id, $dto);

        $this->assertTrue($result->categories()->where('categories.id', $category->id)->exists());
        $this->assertTrue($result->products()->where('products.id', $product->id)->exists());
    }

    public function test_execute_throws_when_the_promotion_does_not_exist(): void
    {
        $this->mock(PromotionRepositoryInterface::class, function ($mock) {
            $mock->shouldReceive('find')->once()->with(999)->andReturn(null);
            $mock->shouldNotReceive('update');
        });

        $this->expectException(PromotionNotFoundException::class);

        app(UpdatePromotionAction::class)->execute(999, $this->makeDto());
    }
}
