<?php

namespace Tests\Feature\Catalog;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Filtering tests exercise plain SQL filters only (no `search` keyword), so the
        // Meilisearch branch of ListProductsAction is never reached here — see the
        // final report for why the search-keyword path itself is out of scope.
        config(['scout.driver' => 'null']);
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

        $response = $this->getJson('/api/v1/catalog/products?in_stock=true');

        $response->assertOk();
        $slugs = $this->slugsIn($response);
        $this->assertContains($inStock->slug, $slugs);
        $this->assertNotContains($outOfStock->slug, $slugs);
    }
}
