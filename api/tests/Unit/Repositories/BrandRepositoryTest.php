<?php

namespace Tests\Unit\Repositories;

use App\Api\V1\Repositories\BrandRepository;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private BrandRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(BrandRepository::class);
    }

    private function makeBrand(array $overrides = []): Brand
    {
        return Brand::create(array_merge([
            'name' => 'Brand-'.uniqid(),
            'slug' => 'brand-'.uniqid(),
        ], $overrides));
    }

    private function makeProduct(Brand $brand, string $status = 'active'): Product
    {
        return Product::create([
            'brand_id' => $brand->id,
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => $status,
        ]);
    }

    // --- all ---

    public function test_all_returns_every_brand(): void
    {
        $this->makeBrand();
        $this->makeBrand();

        $result = $this->repository->all();

        $this->assertCount(2, $result);
    }

    public function test_all_returns_empty_collection_when_no_brands_exist(): void
    {
        $result = $this->repository->all();

        $this->assertCount(0, $result);
    }

    // --- find ---

    public function test_find_returns_the_brand(): void
    {
        $brand = $this->makeBrand();

        $result = $this->repository->find($brand->id);

        $this->assertNotNull($result);
        $this->assertSame($brand->id, $result->id);
    }

    public function test_find_returns_null_when_brand_does_not_exist(): void
    {
        $result = $this->repository->find(999999);

        $this->assertNull($result);
    }

    // --- create ---

    public function test_create_persists_a_new_brand(): void
    {
        $result = $this->repository->create([
            'name' => 'New Brand',
            'slug' => 'new-brand',
        ]);

        $this->assertNotNull($result->id);
        $this->assertDatabaseHas('brands', [
            'id' => $result->id,
            'name' => 'New Brand',
            'slug' => 'new-brand',
        ]);
    }

    // --- update ---

    public function test_update_persists_the_given_data_and_returns_the_brand(): void
    {
        $brand = $this->makeBrand(['name' => 'Old Name']);

        $result = $this->repository->update($brand, ['name' => 'New Name']);

        $this->assertSame('New Name', $result->name);
        $this->assertSame('New Name', $brand->fresh()->name);
    }

    // --- delete ---

    public function test_delete_removes_the_brand_and_returns_true(): void
    {
        $brand = $this->makeBrand();

        $result = $this->repository->delete($brand);

        $this->assertTrue($result);
        $this->assertNull(Brand::find($brand->id));
    }

    // --- getBrandsWithActiveProductsCount ---

    public function test_get_brands_with_active_products_count_counts_only_active_products(): void
    {
        $brandA = $this->makeBrand(['name' => 'Alpha']);
        $this->makeProduct($brandA, 'active');
        $this->makeProduct($brandA, 'active');
        $this->makeProduct($brandA, 'draft');

        $brandB = $this->makeBrand(['name' => 'Beta']);
        $this->makeProduct($brandB, 'archived');

        $result = $this->repository->getBrandsWithActiveProductsCount();

        $alpha = $result->firstWhere('id', $brandA->id);
        $beta = $result->firstWhere('id', $brandB->id);
        $this->assertSame(2, $alpha->products_count);
        $this->assertSame(0, $beta->products_count);
    }

    public function test_get_brands_with_active_products_count_orders_brands_by_name(): void
    {
        $this->makeBrand(['name' => 'Zeta']);
        $this->makeBrand(['name' => 'Alpha']);
        $this->makeBrand(['name' => 'Mu']);

        $result = $this->repository->getBrandsWithActiveProductsCount();

        $this->assertSame(['Alpha', 'Mu', 'Zeta'], $result->pluck('name')->all());
    }

    public function test_get_brands_with_active_products_count_returns_zero_for_brand_without_products(): void
    {
        $brand = $this->makeBrand();

        $result = $this->repository->getBrandsWithActiveProductsCount();

        $this->assertSame(0, $result->first()->products_count);
    }

    public function test_get_brands_with_active_products_count_scopes_to_the_given_category_ids(): void
    {
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);
        $otherCategory = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'B', 'en' => 'B'], 'order' => 0]);

        $brand = $this->makeBrand();
        $inCategory = $this->makeProduct($brand, 'active');
        $inCategory->categories()->attach($category->id);
        $outsideCategory = $this->makeProduct($brand, 'active');
        $outsideCategory->categories()->attach($otherCategory->id);

        $result = $this->repository->getBrandsWithActiveProductsCount([$category->id]);

        $this->assertSame(1, $result->firstWhere('id', $brand->id)->products_count);
    }
}
