<?php

namespace Tests\Feature\Catalog;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithMeilisearch;
use Tests\TestCase;

class CatalogControllerTest extends TestCase
{
    use InteractsWithMeilisearch, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Product fixtures created below are indexed into the real Meilisearch
        // container synchronously (SCOUT_AFTER_COMMIT=false in phpunit.xml, since
        // RefreshDatabase's transaction never commits). Meilisearch itself isn't
        // part of that transaction, so leftover documents from a previous test
        // must be cleared explicitly rather than relying on rollback.
        $this->flushMeilisearchIndex();
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

    private function makeVariant(Product $product, float $price, int $stock = 10, int $reserved = 0, ?float $oldPrice = null): ProductVariant
    {
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => $price,
            'old_price' => $oldPrice,
        ]);

        $warehouse = Warehouse::create(['name' => 'Main']);
        Stock::create([
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => $stock,
            'reserved' => $reserved,
        ]);

        return $variant;
    }

    private function slugsIn($response): array
    {
        return collect($response->json('data.data'))->pluck('slug')->all();
    }

    public function test_products_only_returns_active_products(): void
    {
        $active = $this->makeProduct('active');
        $this->makeVariant($active, 100);
        $inactive = $this->makeProduct('inactive');
        $this->makeVariant($inactive, 100);

        $this->reindexAllProducts();
        $response = $this->getJson('/api/v1/catalog/products');

        $response->assertOk();
        $slugs = $this->slugsIn($response);
        $this->assertContains($active->slug, $slugs);
        $this->assertNotContains($inactive->slug, $slugs);
    }

    public function test_products_can_be_filtered_by_category(): void
    {
        $categoryA = Category::create(['slug' => 'cat-a-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);
        $categoryB = Category::create(['slug' => 'cat-b-'.uniqid(), 'name' => ['uk' => 'B', 'en' => 'B'], 'order' => 0]);

        $productA = $this->makeProduct();
        $productA->categories()->attach($categoryA->id);
        $this->makeVariant($productA, 100);

        $productB = $this->makeProduct();
        $productB->categories()->attach($categoryB->id);
        $this->makeVariant($productB, 100);

        $this->reindexAllProducts();
        $response = $this->getJson('/api/v1/catalog/products?category='.$categoryA->slug);

        $response->assertOk();
        $slugs = $this->slugsIn($response);
        $this->assertContains($productA->slug, $slugs);
        $this->assertNotContains($productB->slug, $slugs);
    }

    public function test_products_can_be_filtered_by_brand(): void
    {
        $brandA = Brand::create(['name' => 'Brand A', 'slug' => 'brand-a-'.uniqid()]);
        $brandB = Brand::create(['name' => 'Brand B', 'slug' => 'brand-b-'.uniqid()]);

        $productA = $this->makeProduct('active', $brandA);
        $this->makeVariant($productA, 100);
        $productB = $this->makeProduct('active', $brandB);
        $this->makeVariant($productB, 100);

        $this->reindexAllProducts();
        $response = $this->getJson('/api/v1/catalog/products?brand='.$brandA->slug);

        $response->assertOk();
        $slugs = $this->slugsIn($response);
        $this->assertContains($productA->slug, $slugs);
        $this->assertNotContains($productB->slug, $slugs);
    }

    public function test_products_can_be_filtered_by_price_range(): void
    {
        $cheap = $this->makeProduct();
        $this->makeVariant($cheap, 50);
        $expensive = $this->makeProduct();
        $this->makeVariant($expensive, 500);

        $this->reindexAllProducts();
        $response = $this->getJson('/api/v1/catalog/products?price_from=100&price_to=1000');

        $response->assertOk();
        $slugs = $this->slugsIn($response);
        $this->assertContains($expensive->slug, $slugs);
        $this->assertNotContains($cheap->slug, $slugs);
    }

    public function test_products_can_be_filtered_by_the_discount_flag(): void
    {
        $discounted = $this->makeProduct();
        $this->makeVariant($discounted, 80, oldPrice: 100);
        $regular = $this->makeProduct();
        $this->makeVariant($regular, 80);

        $this->reindexAllProducts();
        $response = $this->getJson('/api/v1/catalog/products?discounts=true');

        $response->assertOk();
        $slugs = $this->slugsIn($response);
        $this->assertContains($discounted->slug, $slugs);
        $this->assertNotContains($regular->slug, $slugs);
    }

    public function test_products_can_be_filtered_by_the_in_stock_flag(): void
    {
        $inStock = $this->makeProduct();
        $this->makeVariant($inStock, 100, stock: 5, reserved: 0);
        $outOfStock = $this->makeProduct();
        $this->makeVariant($outOfStock, 100, stock: 5, reserved: 5);

        $this->reindexAllProducts();
        $response = $this->getJson('/api/v1/catalog/products?in_stock=true');

        $response->assertOk();
        $slugs = $this->slugsIn($response);
        $this->assertContains($inStock->slug, $slugs);
        $this->assertNotContains($outOfStock->slug, $slugs);
    }

    public function test_related_products_prefers_the_same_category(): void
    {
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);
        $otherCategory = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'B', 'en' => 'B'], 'order' => 0]);

        $product = $this->makeProduct();
        $product->categories()->attach($category->id);
        $this->makeVariant($product, 100);

        $sameCategory = $this->makeProduct();
        $sameCategory->categories()->attach($category->id);
        $this->makeVariant($sameCategory, 100);

        $otherCategoryProduct = $this->makeProduct();
        $otherCategoryProduct->categories()->attach($otherCategory->id);
        $this->makeVariant($otherCategoryProduct, 100);

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}/related");

        $response->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug')->all();
        $this->assertContains($sameCategory->slug, $slugs);
        $this->assertNotContains($product->slug, $slugs);
    }

    public function test_related_products_tops_up_with_other_active_products_when_category_is_short(): void
    {
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);

        $product = $this->makeProduct();
        $product->categories()->attach($category->id);
        $this->makeVariant($product, 100);

        $uncategorized = $this->makeProduct();
        $this->makeVariant($uncategorized, 100);

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}/related");

        $response->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug')->all();
        $this->assertContains($uncategorized->slug, $slugs);
    }

    public function test_related_products_excludes_inactive_products(): void
    {
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);

        $product = $this->makeProduct();
        $product->categories()->attach($category->id);
        $this->makeVariant($product, 100);

        $inactiveSameCategory = $this->makeProduct('inactive');
        $inactiveSameCategory->categories()->attach($category->id);
        $this->makeVariant($inactiveSameCategory, 100);

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}/related");

        $response->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug')->all();
        $this->assertNotContains($inactiveSameCategory->slug, $slugs);
    }

    public function test_related_products_returns_404_for_an_unknown_slug(): void
    {
        $response = $this->getJson('/api/v1/catalog/products/does-not-exist/related');

        $response->assertNotFound();
    }

    public function test_related_products_returns_404_for_an_inactive_product(): void
    {
        $inactive = $this->makeProduct('inactive');

        $response = $this->getJson("/api/v1/catalog/products/{$inactive->slug}/related");

        $response->assertNotFound();
    }

    public function test_categories_returns_parent_categories_with_their_children(): void
    {
        $parent = Category::create(['slug' => 'parent-'.uniqid(), 'name' => ['uk' => 'P', 'en' => 'P'], 'order' => 0]);
        $child = Category::create(['slug' => 'child-'.uniqid(), 'name' => ['uk' => 'C', 'en' => 'C'], 'order' => 0, 'parent_id' => $parent->id]);

        $response = $this->getJson('/api/v1/catalog/categories');

        $response->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug')->all();
        $this->assertContains($parent->slug, $slugs);
        $this->assertNotContains($child->slug, $slugs);

        $parentPayload = collect($response->json('data'))->firstWhere('slug', $parent->slug);
        $this->assertSame($child->slug, $parentPayload['children'][0]['slug']);
    }

    public function test_brands_returns_brands_with_their_active_product_count(): void
    {
        $brand = Brand::create(['name' => 'Brand A', 'slug' => 'brand-'.uniqid()]);
        $activeProduct = $this->makeProduct('active', $brand);
        $this->makeVariant($activeProduct, 100);
        $inactiveProduct = $this->makeProduct('inactive', $brand);
        $this->makeVariant($inactiveProduct, 100);

        $response = $this->getJson('/api/v1/catalog/brands');

        $response->assertOk();
        $payload = collect($response->json('data'))->firstWhere('slug', $brand->slug);
        $this->assertSame(1, $payload['productsCount']);
    }

    public function test_filters_returns_the_price_range_and_attributes_with_values(): void
    {
        $product = $this->makeProduct();
        $this->makeVariant($product, 50);
        $this->makeVariant($product, 500);

        $attribute = Attribute::create(['code' => 'color', 'name' => ['uk' => 'Колір', 'en' => 'Color'], 'type' => 'select']);
        AttributeValue::create(['attribute_id' => $attribute->id, 'value' => ['uk' => 'Червоний', 'en' => 'Red']]);

        $this->reindexAllProducts();
        $response = $this->getJson('/api/v1/catalog/filters');

        $response->assertOk()
            ->assertJsonPath('data.price.min', 50)
            ->assertJsonPath('data.price.max', 500);
        $codes = collect($response->json('data.attributes'))->pluck('code')->all();
        $this->assertContains('color', $codes);
    }

    public function test_filters_scopes_the_price_range_to_the_given_category(): void
    {
        $categoryA = Category::create(['slug' => 'cat-a-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);
        $categoryB = Category::create(['slug' => 'cat-b-'.uniqid(), 'name' => ['uk' => 'B', 'en' => 'B'], 'order' => 0]);

        $productA = $this->makeProduct();
        $productA->categories()->attach($categoryA->id);
        $this->makeVariant($productA, 16700);

        $productB = $this->makeProduct();
        $productB->categories()->attach($categoryB->id);
        $this->makeVariant($productB, 99);

        $this->reindexAllProducts();
        $response = $this->getJson('/api/v1/catalog/filters?category='.$categoryA->slug);

        $response->assertOk()
            ->assertJsonPath('data.price.min', 16700)
            ->assertJsonPath('data.price.max', 16700);
    }

    public function test_brands_scopes_the_product_count_to_the_given_category(): void
    {
        $categoryA = Category::create(['slug' => 'cat-a-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);
        $categoryB = Category::create(['slug' => 'cat-b-'.uniqid(), 'name' => ['uk' => 'B', 'en' => 'B'], 'order' => 0]);

        $brand = Brand::create(['name' => 'Brand A', 'slug' => 'brand-'.uniqid()]);
        $productA = $this->makeProduct('active', $brand);
        $productA->categories()->attach($categoryA->id);
        $this->makeVariant($productA, 100);
        $productB = $this->makeProduct('active', $brand);
        $productB->categories()->attach($categoryB->id);
        $this->makeVariant($productB, 100);

        $response = $this->getJson('/api/v1/catalog/brands?category='.$categoryA->slug);

        $response->assertOk();
        $payload = collect($response->json('data'))->firstWhere('slug', $brand->slug);
        $this->assertSame(1, $payload['productsCount']);
    }

    public function test_product_returns_an_active_products_details_by_slug(): void
    {
        $product = $this->makeProduct();
        $this->makeVariant($product, 100);

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}");

        $response->assertOk()->assertJsonPath('data.slug', $product->slug);
    }

    public function test_product_increments_the_views_count(): void
    {
        $product = $this->makeProduct();
        $this->makeVariant($product, 100);

        $this->getJson("/api/v1/catalog/products/{$product->slug}")->assertOk();

        $this->assertSame(1, $product->fresh()->views_count);
    }

    public function test_product_returns_404_for_an_unknown_slug(): void
    {
        $response = $this->getJson('/api/v1/catalog/products/does-not-exist');

        $response->assertNotFound();
    }

    public function test_product_returns_404_for_an_inactive_product(): void
    {
        $inactive = $this->makeProduct('inactive');

        $response = $this->getJson("/api/v1/catalog/products/{$inactive->slug}");

        $response->assertNotFound();
    }

    public function test_random_products_only_returns_active_products(): void
    {
        $active = $this->makeProduct('active');
        $this->makeVariant($active, 100);
        $inactive = $this->makeProduct('inactive');
        $this->makeVariant($inactive, 100);

        $response = $this->getJson('/api/v1/catalog/products/random');

        $response->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug')->all();
        $this->assertContains($active->slug, $slugs);
        $this->assertNotContains($inactive->slug, $slugs);
    }

    public function test_random_products_returns_at_most_five_products(): void
    {
        for ($i = 0; $i < 7; $i++) {
            $product = $this->makeProduct();
            $this->makeVariant($product, 100);
        }

        $response = $this->getJson('/api/v1/catalog/products/random');

        $response->assertOk();
        $this->assertCount(5, $response->json('data'));
    }
}
