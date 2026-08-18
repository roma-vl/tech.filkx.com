<?php

namespace Tests\Unit\Repositories;

use App\Api\V1\Repositories\ProductRepository;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(ProductRepository::class);
    }

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ], $overrides));
    }

    private function makeVariant(Product $product, float $price, ?float $oldPrice = null): ProductVariant
    {
        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => $price,
            'old_price' => $oldPrice,
        ]);
    }

    public function test_all_returns_every_product_regardless_of_status(): void
    {
        $active = $this->makeProduct(['status' => 'active']);
        $draft = $this->makeProduct(['status' => 'draft']);

        $result = $this->repository->all();

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $active->id));
        $this->assertTrue($result->contains('id', $draft->id));
    }

    public function test_find_returns_the_matching_product(): void
    {
        $product = $this->makeProduct();

        $found = $this->repository->find($product->id);

        $this->assertNotNull($found);
        $this->assertSame($product->id, $found->id);
    }

    public function test_find_returns_null_when_no_product_matches(): void
    {
        $this->assertNull($this->repository->find(999999));
    }

    public function test_create_persists_a_new_product(): void
    {
        $product = $this->repository->create([
            'slug' => 'new-product',
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('products', ['id' => $product->id, 'slug' => 'new-product']);
    }

    public function test_update_persists_changes_to_the_product(): void
    {
        $product = $this->makeProduct(['status' => 'draft']);

        $updated = $this->repository->update($product, ['status' => 'active']);

        $this->assertSame('active', $updated->status);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'active']);
    }

    public function test_delete_removes_the_product_and_returns_true(): void
    {
        $product = $this->makeProduct();

        $result = $this->repository->delete($product);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_slug_exists_returns_true_when_a_product_has_the_slug(): void
    {
        $product = $this->makeProduct(['slug' => 'taken-slug']);

        $this->assertTrue($this->repository->slugExists('taken-slug'));
    }

    public function test_slug_exists_returns_false_when_no_product_has_the_slug(): void
    {
        $this->assertFalse($this->repository->slugExists('free-slug'));
    }

    public function test_query_active_only_includes_active_products(): void
    {
        $active = $this->makeProduct(['status' => 'active']);
        $this->makeProduct(['status' => 'draft']);
        $this->makeProduct(['status' => 'archived']);

        $result = $this->repository->queryActive()->get();

        $this->assertCount(1, $result);
        $this->assertSame($active->id, $result->first()->id);
    }

    public function test_find_by_slug_returns_the_matching_active_product(): void
    {
        $product = $this->makeProduct(['slug' => 'my-slug']);

        $found = $this->repository->findBySlug('my-slug');

        $this->assertNotNull($found);
        $this->assertSame($product->id, $found->id);
    }

    public function test_find_by_slug_returns_null_when_the_product_is_not_active(): void
    {
        $this->makeProduct(['slug' => 'inactive-slug', 'status' => 'draft']);

        $this->assertNull($this->repository->findBySlug('inactive-slug'));
    }

    public function test_find_by_slug_returns_null_when_nothing_matches(): void
    {
        $this->assertNull($this->repository->findBySlug('does-not-exist'));
    }

    public function test_find_by_slug_resolves_a_numeric_slug_as_an_id_first(): void
    {
        $product = $this->makeProduct(['slug' => 'text-slug']);

        $found = $this->repository->findBySlug((string) $product->id);

        $this->assertNotNull($found);
        $this->assertSame($product->id, $found->id);
    }

    public function test_find_by_slug_falls_back_to_a_literal_numeric_slug_match(): void
    {
        // No product has this id, but one has this exact numeric string as its slug.
        $product = $this->makeProduct(['slug' => '424242']);

        $found = $this->repository->findBySlug('424242');

        $this->assertNotNull($found);
        $this->assertSame($product->id, $found->id);
    }

    public function test_find_by_slug_does_not_resolve_a_numeric_id_to_an_inactive_product(): void
    {
        $inactive = $this->makeProduct(['slug' => 'draft-slug', 'status' => 'draft']);

        $this->assertNull($this->repository->findBySlug((string) $inactive->id));
    }

    public function test_get_hot_deals_includes_products_flagged_as_hot(): void
    {
        $hot = $this->makeProduct(['is_hot' => true]);
        $this->makeVariant($hot, 100);
        $regular = $this->makeProduct();
        $this->makeVariant($regular, 100);

        $result = $this->repository->getHotDeals();

        $this->assertTrue($result->contains('id', $hot->id));
        $this->assertFalse($result->contains('id', $regular->id));
    }

    public function test_get_hot_deals_includes_products_with_a_discounted_variant(): void
    {
        $discounted = $this->makeProduct();
        $this->makeVariant($discounted, 80, oldPrice: 100);
        $regular = $this->makeProduct();
        $this->makeVariant($regular, 80);

        $result = $this->repository->getHotDeals();

        $this->assertTrue($result->contains('id', $discounted->id));
        $this->assertFalse($result->contains('id', $regular->id));
    }

    public function test_get_hot_deals_excludes_inactive_products(): void
    {
        $inactiveHot = $this->makeProduct(['is_hot' => true, 'status' => 'draft']);
        $this->makeVariant($inactiveHot, 100);

        $result = $this->repository->getHotDeals();

        $this->assertFalse($result->contains('id', $inactiveHot->id));
    }

    public function test_get_hot_deals_respects_the_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $product = $this->makeProduct(['is_hot' => true]);
            $this->makeVariant($product, 100);
        }

        $result = $this->repository->getHotDeals(3);

        $this->assertCount(3, $result);
    }

    public function test_get_recommended_includes_only_recommended_active_products(): void
    {
        $recommended = $this->makeProduct(['is_recommended' => true]);
        $notRecommended = $this->makeProduct(['is_recommended' => false]);
        $inactiveRecommended = $this->makeProduct(['is_recommended' => true, 'status' => 'draft']);

        $result = $this->repository->getRecommended();

        $this->assertTrue($result->contains('id', $recommended->id));
        $this->assertFalse($result->contains('id', $notRecommended->id));
        $this->assertFalse($result->contains('id', $inactiveRecommended->id));
    }

    public function test_get_recommended_respects_the_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->makeProduct(['is_recommended' => true]);
        }

        $result = $this->repository->getRecommended(2);

        $this->assertCount(2, $result);
    }

    public function test_get_related_prefers_products_from_the_same_category(): void
    {
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);
        $other = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'B', 'en' => 'B'], 'order' => 0]);

        $product = $this->makeProduct();
        $product->categories()->attach($category->id);

        $sameCategory = $this->makeProduct();
        $sameCategory->categories()->attach($category->id);

        $otherCategory = $this->makeProduct();
        $otherCategory->categories()->attach($other->id);

        $result = $this->repository->getRelated($product, 1);

        $this->assertCount(1, $result);
        $this->assertSame($sameCategory->id, $result->first()->id);
    }

    public function test_get_related_excludes_the_product_itself(): void
    {
        $product = $this->makeProduct();

        $result = $this->repository->getRelated($product, 8);

        $this->assertFalse($result->contains('id', $product->id));
    }

    public function test_get_related_tops_up_with_random_active_products_when_the_category_is_short(): void
    {
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);

        $product = $this->makeProduct();
        $product->categories()->attach($category->id);

        $uncategorized = $this->makeProduct();

        $result = $this->repository->getRelated($product, 5);

        $this->assertCount(1, $result);
        $this->assertSame($uncategorized->id, $result->first()->id);
    }

    public function test_get_related_excludes_inactive_products_from_the_fallback(): void
    {
        $product = $this->makeProduct();
        $this->makeProduct(['status' => 'draft']);

        $result = $this->repository->getRelated($product, 5);

        $this->assertCount(0, $result);
    }

    public function test_get_random_fallback_excludes_given_ids(): void
    {
        $kept = $this->makeProduct();
        $excluded = $this->makeProduct();

        $result = $this->repository->getRandomFallback([$excluded->id], 5);

        $this->assertTrue($result->contains('id', $kept->id));
        $this->assertFalse($result->contains('id', $excluded->id));
    }

    public function test_get_random_fallback_excludes_inactive_products(): void
    {
        $this->makeProduct(['status' => 'draft']);

        $result = $this->repository->getRandomFallback([], 5);

        $this->assertCount(0, $result);
    }

    public function test_get_random_fallback_respects_the_limit(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->makeProduct();
        }

        $result = $this->repository->getRandomFallback([], 2);

        $this->assertCount(2, $result);
    }
}
