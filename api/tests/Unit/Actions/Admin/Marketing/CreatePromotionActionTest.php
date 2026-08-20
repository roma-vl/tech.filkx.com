<?php

namespace Tests\Unit\Actions\Admin\Marketing;

use App\Api\Admin\Actions\Marketing\CreatePromotionAction;
use App\Api\Admin\Dto\PromotionDto;
use App\Api\V1\Repositories\PromotionRepositoryInterface;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatePromotionActionTest extends TestCase
{
    use RefreshDatabase;

    private function makeDto(array $categoryIds = [], array $productIds = []): PromotionDto
    {
        return new PromotionDto(
            name: 'Summer Sale',
            description: null,
            type: 'percent',
            amount: 15,
            startDate: null,
            endDate: null,
            isActive: true,
            categoryIds: $categoryIds,
            productIds: $productIds
        );
    }

    public function test_execute_creates_the_promotion_via_the_repository(): void
    {
        $dto = $this->makeDto();
        $promotion = Promotion::create($dto->toArray());

        $this->mock(PromotionRepositoryInterface::class, function ($mock) use ($dto, $promotion) {
            $mock->shouldReceive('create')->once()->with($dto->toArray())->andReturn($promotion);
        });

        $result = app(CreatePromotionAction::class)->execute($dto);

        $this->assertSame($promotion->id, $result->id);
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
        $promotion = Promotion::create($dto->toArray());

        $this->mock(PromotionRepositoryInterface::class, function ($mock) use ($promotion) {
            $mock->shouldReceive('create')->once()->andReturn($promotion);
        });

        $result = app(CreatePromotionAction::class)->execute($dto);

        $this->assertTrue($result->categories()->where('categories.id', $category->id)->exists());
        $this->assertTrue($result->products()->where('products.id', $product->id)->exists());
    }
}
