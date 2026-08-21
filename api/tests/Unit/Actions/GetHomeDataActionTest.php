<?php

namespace Tests\Unit\Actions;

use App\Api\V1\Actions\GetHomeDataAction;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class GetHomeDataActionTest extends TestCase
{
    use RefreshDatabase;

    private GetHomeDataAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        // Model creation triggers Scout's Searchable observer; keep it off the network.
        config(['scout.driver' => 'null']);

        $this->action = app(GetHomeDataAction::class);
    }

    private function makeProduct(string $status = 'active', ?Brand $brand = null): Product
    {
        return Product::create([
            'brand_id' => $brand?->id,
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => $status,
        ]);
    }

    private function requestWith(array $query = []): Request
    {
        return Request::create('/api/v1/catalog/home', 'GET', $query);
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

    public function test_execute_recommends_products_sharing_a_category_with_a_wishlisted_product(): void
    {
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);

        $seed = $this->makeProduct();
        $seed->categories()->attach($category->id);

        $related = $this->makeProduct();
        $related->categories()->attach($category->id);

        $unrelated = $this->makeProduct();

        $result = $this->action->execute($this->requestWith(['wishlist_ids' => (string) $seed->id]));

        $recommendedIds = $result['recommended']->pluck('id')->all();
        $this->assertContains($related->id, $recommendedIds);
        $this->assertNotContains($seed->id, $recommendedIds);
    }

    public function test_execute_recommends_products_sharing_a_brand_with_a_viewed_product(): void
    {
        $brand = Brand::create(['name' => 'Brand', 'slug' => 'brand-'.uniqid()]);

        $seed = $this->makeProduct('active', $brand);
        $related = $this->makeProduct('active', $brand);

        $result = $this->action->execute($this->requestWith(['viewed_ids' => (string) $seed->id]));

        $recommendedIds = $result['recommended']->pluck('id')->all();
        $this->assertContains($related->id, $recommendedIds);
    }

    public function test_execute_skips_recommendation_matching_when_seed_products_have_no_category_or_brand(): void
    {
        $seed = $this->makeProduct();
        $other = $this->makeProduct();

        $result = $this->action->execute($this->requestWith(['wishlist_ids' => (string) $seed->id]));

        // No category/brand signal on the seed product means the "related by category or
        // brand" branch is skipped entirely; recommendations fall back to random active items.
        $this->assertTrue($result['recommended']->pluck('id')->contains($other->id));
    }

    public function test_execute_includes_products_explicitly_marked_as_recommended(): void
    {
        $promoted = $this->makeProduct();
        $promoted->update(['is_recommended' => true]);

        $result = $this->action->execute($this->requestWith());

        $recommendedIds = $result['recommended']->pluck('id')->all();
        $this->assertContains($promoted->id, $recommendedIds);
    }

    public function test_execute_falls_back_to_other_active_products_when_fewer_than_eight_are_recommended(): void
    {
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);

        $seed = $this->makeProduct();
        $seed->categories()->attach($category->id);

        $related = $this->makeProduct();
        $related->categories()->attach($category->id);

        $fallbackCandidate = $this->makeProduct();

        $result = $this->action->execute($this->requestWith(['wishlist_ids' => (string) $seed->id]));

        $recommendedIds = $result['recommended']->pluck('id')->all();
        $this->assertContains($related->id, $recommendedIds);
        $this->assertContains($fallbackCandidate->id, $recommendedIds);
        $this->assertLessThanOrEqual(8, $result['recommended']->count());
    }

    public function test_execute_returns_active_banners_categories_and_flash_deals(): void
    {
        $product = $this->makeProduct();
        $product->update(['is_hot' => true]);

        $result = $this->action->execute($this->requestWith());

        $this->assertArrayHasKey('banners', $result);
        $this->assertArrayHasKey('categories', $result);
        $this->assertArrayHasKey('flash_deals', $result);
        $this->assertArrayHasKey('recommended', $result);
        $this->assertTrue($result['flash_deals']->pluck('id')->contains($product->id));
    }

    public function test_execute_only_includes_flash_deals_that_are_hot_or_genuinely_discounted(): void
    {
        $hot = $this->makeProduct();
        $hot->update(['is_hot' => true]);
        $this->makeVariant($hot, 100);

        $discounted = $this->makeProduct();
        $this->makeVariant($discounted, 80, 100);

        $plain = $this->makeProduct();
        $this->makeVariant($plain, 80);

        $result = $this->action->execute($this->requestWith());

        $flashDealIds = $result['flash_deals']->pluck('id')->all();
        $this->assertContains($hot->id, $flashDealIds);
        $this->assertContains($discounted->id, $flashDealIds);
        $this->assertNotContains($plain->id, $flashDealIds);
    }

    public function test_execute_returns_the_same_flash_deals_on_repeated_calls_within_the_same_hour(): void
    {
        for ($i = 0; $i < 6; $i++) {
            $product = $this->makeProduct();
            $this->makeVariant($product, 80, 100);
        }

        $first = $this->action->execute($this->requestWith())['flash_deals']->pluck('id')->all();
        $second = $this->action->execute($this->requestWith())['flash_deals']->pluck('id')->all();

        // Guards against reintroducing DB-level inRandomOrder($seed) - Postgres ignores the
        // seed argument and reshuffles on every call, which would make this flaky/fail here.
        $this->assertSame($first, $second);
    }
}
