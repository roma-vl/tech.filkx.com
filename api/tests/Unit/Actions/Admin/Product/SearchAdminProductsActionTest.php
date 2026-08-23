<?php

namespace Tests\Unit\Actions\Admin\Product;

use App\Api\Admin\Actions\Product\SearchAdminProductsAction;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithMeilisearch;
use Tests\TestCase;

class SearchAdminProductsActionTest extends TestCase
{
    use InteractsWithMeilisearch, RefreshDatabase;

    private SearchAdminProductsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flushMeilisearchIndex();

        $this->action = app(SearchAdminProductsAction::class);
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

    private function makeVariant(Product $product, float $price, array $dimensions = []): ProductVariant
    {
        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => $price,
            'dimensions' => $dimensions,
        ]);
    }

    private function addStock(ProductVariant $variant, int $quantity): void
    {
        $warehouse = Warehouse::create(['name' => 'Warehouse '.uniqid()]);
        Stock::create(['variant_id' => $variant->id, 'warehouse_id' => $warehouse->id, 'quantity' => $quantity]);
    }

    public function test_execute_paginates_results(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->makeProduct();
        }

        $result = $this->action->execute(['perPage' => 2, 'page' => 1]);

        $this->assertCount(2, $result->items());
        $this->assertSame(5, $result->total());
        $this->assertSame(3, $result->lastPage());
    }

    public function test_execute_filters_by_category(): void
    {
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'A', 'en' => 'A'], 'order' => 0]);
        $inCategory = $this->makeProduct();
        $inCategory->categories()->attach($category->id);
        $this->makeProduct();

        $result = $this->action->execute(['categoryId' => $category->id]);

        $this->assertSame(1, $result->total());
        $this->assertSame($inCategory->id, $result->items()[0]->id);
    }

    public function test_execute_filters_by_status(): void
    {
        $this->makeProduct(['status' => 'active']);
        $this->makeProduct(['status' => 'draft']);

        $result = $this->action->execute(['status' => 'draft']);

        $this->assertSame(1, $result->total());
        $this->assertSame('draft', $result->items()[0]->status);
    }

    public function test_execute_filters_by_hot(): void
    {
        $hot = $this->makeProduct(['is_hot' => true]);
        $this->makeProduct(['is_hot' => false]);

        $result = $this->action->execute(['hot' => true]);

        $this->assertSame(1, $result->total());
        $this->assertSame($hot->id, $result->items()[0]->id);
    }

    public function test_execute_filters_by_recommended(): void
    {
        $recommended = $this->makeProduct(['is_recommended' => true]);
        $this->makeProduct(['is_recommended' => false]);

        $result = $this->action->execute(['recommended' => true]);

        $this->assertSame(1, $result->total());
        $this->assertSame($recommended->id, $result->items()[0]->id);
    }

    public function test_execute_filters_products_with_a_real_image(): void
    {
        $withImage = $this->makeProduct();
        $this->makeVariant($withImage, 100, ['images' => [['url' => 'https://example.com/a.jpg', 'isPrimary' => true]]]);
        $withoutImage = $this->makeProduct();
        $this->makeVariant($withoutImage, 100);

        $result = $this->action->execute(['hasImage' => 'with']);

        $this->assertSame(1, $result->total());
        $this->assertSame($withImage->id, $result->items()[0]->id);
    }

    public function test_execute_filters_products_without_an_image(): void
    {
        $withImage = $this->makeProduct();
        $this->makeVariant($withImage, 100, ['images' => [['url' => 'https://example.com/a.jpg']]]);
        $withoutImage = $this->makeProduct();
        $this->makeVariant($withoutImage, 100);
        $noVariantAtAll = $this->makeProduct();

        $result = $this->action->execute(['hasImage' => 'without']);

        $ids = collect($result->items())->pluck('id')->all();
        $this->assertContains($withoutImage->id, $ids);
        $this->assertContains($noVariantAtAll->id, $ids);
        $this->assertNotContains($withImage->id, $ids);
    }

    public function test_execute_filters_products_in_stock(): void
    {
        $inStock = $this->makeProduct();
        $this->addStock($this->makeVariant($inStock, 100), 5);
        $outOfStock = $this->makeProduct();
        $this->addStock($this->makeVariant($outOfStock, 100), 0);

        $result = $this->action->execute(['stock' => 'inStock']);

        $this->assertSame(1, $result->total());
        $this->assertSame($inStock->id, $result->items()[0]->id);
    }

    public function test_execute_filters_products_out_of_stock(): void
    {
        $inStock = $this->makeProduct();
        $this->addStock($this->makeVariant($inStock, 100), 5);
        $outOfStock = $this->makeProduct();
        $this->makeVariant($outOfStock, 100);

        $result = $this->action->execute(['stock' => 'outOfStock']);

        $this->assertSame(1, $result->total());
        $this->assertSame($outOfStock->id, $result->items()[0]->id);
    }

    public function test_execute_sorts_by_name_ascending_by_default(): void
    {
        $this->makeProduct(['name' => ['uk' => 'Яблуко', 'en' => 'Apple']]);
        $this->makeProduct(['name' => ['uk' => 'Апельсин', 'en' => 'Orange']]);

        $result = $this->action->execute([]);

        $names = collect($result->items())->pluck('name.uk')->all();
        $this->assertSame(['Апельсин', 'Яблуко'], $names);
    }

    public function test_execute_sorts_by_price_ascending(): void
    {
        $cheap = $this->makeProduct();
        $this->makeVariant($cheap, 50);
        $expensive = $this->makeProduct();
        $this->makeVariant($expensive, 500);

        $result = $this->action->execute(['sort' => 'price-asc']);

        $this->assertSame($cheap->id, $result->items()[0]->id);
        $this->assertSame($expensive->id, $result->items()[1]->id);
    }

    public function test_execute_sorts_by_total_stock_descending(): void
    {
        $lowStock = $this->makeProduct();
        $this->addStock($this->makeVariant($lowStock, 100), 2);
        $highStock = $this->makeProduct();
        $this->addStock($this->makeVariant($highStock, 100), 20);

        $result = $this->action->execute(['sort' => 'stock-desc']);

        $this->assertSame($highStock->id, $result->items()[0]->id);
        $this->assertSame($lowStock->id, $result->items()[1]->id);
    }

    public function test_execute_finds_products_by_name_via_search(): void
    {
        $match = $this->makeProduct(['name' => ['uk' => 'Навушники Sony', 'en' => 'Sony Headphones']]);
        $this->makeProduct(['name' => ['uk' => 'Клавіатура', 'en' => 'Keyboard']]);
        $this->reindexAllProducts();

        $result = $this->action->execute(['search' => 'Sony']);

        $this->assertSame(1, $result->total());
        $this->assertSame($match->id, $result->items()[0]->id);
    }

    public function test_execute_finds_products_by_variant_sku_via_search(): void
    {
        $match = $this->makeProduct();
        ProductVariant::create(['product_id' => $match->id, 'sku' => 'IPHONE-15-BLACK', 'price' => 100]);
        $this->makeProduct();
        $this->reindexAllProducts();

        $result = $this->action->execute(['search' => 'IPHONE-15-BLACK']);

        $this->assertSame(1, $result->total());
        $this->assertSame($match->id, $result->items()[0]->id);
    }

    public function test_execute_excludes_soft_deleted_products_from_search(): void
    {
        $product = $this->makeProduct(['name' => ['uk' => 'Унікальна назва', 'en' => 'Unique name']]);
        $this->reindexAllProducts();
        $product->delete();
        $this->waitForMeilisearchIndexing();

        $result = $this->action->execute(['search' => 'Унікальна']);

        $this->assertSame(0, $result->total());
    }

    public function test_execute_ids_returns_matching_ids_ignoring_pagination(): void
    {
        $matching = [];
        for ($i = 0; $i < 3; $i++) {
            $matching[] = $this->makeProduct(['is_hot' => true])->id;
        }
        $this->makeProduct(['is_hot' => false]);

        $ids = $this->action->executeIds(['hot' => true]);

        $this->assertEqualsCanonicalizing($matching, $ids);
    }
}
