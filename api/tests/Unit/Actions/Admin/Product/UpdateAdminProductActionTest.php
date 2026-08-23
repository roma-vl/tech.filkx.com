<?php

namespace Tests\Unit\Actions\Admin\Product;

use App\Api\Admin\Actions\Product\UpdateAdminProductAction;
use App\Api\Admin\Dto\ProductDto;
use App\Api\V1\Exceptions\ProductNotFoundException;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAdminProductActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateAdminProductAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateAdminProductAction::class);
    }

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'draft',
        ], $overrides));
    }

    private function makeCategory(): Category
    {
        return Category::create([
            'slug' => 'cat-'.uniqid(),
            'name' => ['uk' => 'A', 'en' => 'A'],
            'order' => 0,
        ]);
    }

    private function makeDto(int $categoryId, array $overrides = []): ProductDto
    {
        return new ProductDto(
            nameUk: $overrides['nameUk'] ?? 'Товар',
            nameEn: $overrides['nameEn'] ?? 'Product',
            descriptionUk: $overrides['descriptionUk'] ?? null,
            descriptionEn: $overrides['descriptionEn'] ?? null,
            status: $overrides['status'] ?? 'active',
            isHot: $overrides['isHot'] ?? false,
            isRecommended: $overrides['isRecommended'] ?? false,
            brandId: $overrides['brandId'] ?? null,
            categoryId: $categoryId,
            variants: $overrides['variants'] ?? []
        );
    }

    public function test_execute_throws_when_the_product_does_not_exist(): void
    {
        $this->expectException(ProductNotFoundException::class);

        $this->action->execute(999999, $this->makeDto($this->makeCategory()->id));
    }

    public function test_execute_updates_the_product_fields(): void
    {
        $product = $this->makeProduct(['status' => 'draft']);
        $dto = $this->makeDto($this->makeCategory()->id, ['status' => 'active']);

        $updated = $this->action->execute($product->id, $dto);

        $this->assertSame('active', $updated->status);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'status' => 'active']);
    }

    public function test_execute_syncs_the_category(): void
    {
        $product = $this->makeProduct();
        $category = $this->makeCategory();

        $updated = $this->action->execute($product->id, $this->makeDto($category->id));

        $this->assertTrue($updated->categories()->where('categories.id', $category->id)->exists());
    }

    public function test_execute_creates_a_warehouse_when_none_exists(): void
    {
        $product = $this->makeProduct();
        $this->assertSame(0, Warehouse::count());

        $this->action->execute($product->id, $this->makeDto($this->makeCategory()->id));

        $this->assertSame(1, Warehouse::count());
    }

    public function test_execute_updates_an_existing_variant_when_an_id_is_given(): void
    {
        $product = $this->makeProduct();
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'old-sku',
            'price' => 50,
        ]);

        $dto = $this->makeDto($this->makeCategory()->id, [
            'variants' => [
                ['id' => $variant->id, 'sku' => 'new-sku', 'price' => 99, 'stock' => 3],
            ],
        ]);
        $this->action->execute($product->id, $dto);

        $this->assertSame(1, ProductVariant::where('product_id', $product->id)->count());
        $this->assertDatabaseHas('product_variants', ['id' => $variant->id, 'sku' => 'new-sku']);
    }

    public function test_execute_creates_a_new_variant_when_no_id_is_given(): void
    {
        $product = $this->makeProduct();

        $dto = $this->makeDto($this->makeCategory()->id, [
            'variants' => [
                ['sku' => 'brand-new-sku', 'price' => 10, 'stock' => 1],
            ],
        ]);
        $this->action->execute($product->id, $dto);

        $this->assertDatabaseHas('product_variants', ['product_id' => $product->id, 'sku' => 'brand-new-sku']);
    }

    public function test_execute_deletes_variants_that_are_not_present_in_the_payload(): void
    {
        $product = $this->makeProduct();
        $keptVariant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'keep', 'price' => 10]);
        $removedVariant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'remove', 'price' => 10]);

        $dto = $this->makeDto($this->makeCategory()->id, [
            'variants' => [
                ['id' => $keptVariant->id, 'sku' => 'keep', 'price' => 10, 'stock' => 1],
            ],
        ]);
        $this->action->execute($product->id, $dto);

        $this->assertDatabaseHas('product_variants', ['id' => $keptVariant->id]);
        $this->assertDatabaseMissing('product_variants', ['id' => $removedVariant->id]);
    }

    public function test_execute_updates_stock_quantity_for_an_existing_stock_row(): void
    {
        $product = $this->makeProduct();
        $warehouse = Warehouse::create(['name' => 'W', 'address' => 'A']);
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'sku-1', 'price' => 10]);
        Stock::create(['variant_id' => $variant->id, 'warehouse_id' => $warehouse->id, 'quantity' => 1, 'reserved' => 0]);

        $dto = $this->makeDto($this->makeCategory()->id, [
            'variants' => [
                ['id' => $variant->id, 'sku' => 'sku-1', 'price' => 10, 'stock' => 25],
            ],
        ]);
        $this->action->execute($product->id, $dto);

        $this->assertDatabaseHas('stocks', ['variant_id' => $variant->id, 'warehouse_id' => $warehouse->id, 'quantity' => 25]);
        $this->assertSame(1, Stock::where('variant_id', $variant->id)->count());
    }

    public function test_execute_creates_a_stock_row_when_missing_for_the_warehouse(): void
    {
        $product = $this->makeProduct();
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'sku-1', 'price' => 10]);

        $dto = $this->makeDto($this->makeCategory()->id, [
            'variants' => [
                ['id' => $variant->id, 'sku' => 'sku-1', 'price' => 10, 'stock' => 7],
            ],
        ]);
        $this->action->execute($product->id, $dto);

        $this->assertDatabaseHas('stocks', ['variant_id' => $variant->id, 'quantity' => 7]);
    }

    public function test_execute_resyncs_attribute_values_for_a_variant(): void
    {
        $product = $this->makeProduct();
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'sku-1', 'price' => 10]);
        $attribute = Attribute::create(['code' => 'size', 'name' => ['uk' => 'Р', 'en' => 'S'], 'type' => 'text']);
        $stale = ProductAttributeValue::create([
            'product_id' => $product->id,
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'custom_value' => 'stale',
        ]);

        $dto = $this->makeDto($this->makeCategory()->id, [
            'variants' => [
                [
                    'id' => $variant->id,
                    'sku' => 'sku-1',
                    'price' => 10,
                    'stock' => 1,
                    'attributes' => [
                        ['attributeId' => $attribute->id, 'value' => 'fresh'],
                    ],
                ],
            ],
        ]);
        $this->action->execute($product->id, $dto);

        $this->assertDatabaseMissing('product_attribute_values', ['id' => $stale->id]);
        $this->assertDatabaseHas('product_attribute_values', [
            'variant_id' => $variant->id,
            'attribute_id' => $attribute->id,
            'custom_value' => 'fresh',
        ]);
    }

    public function test_execute_skips_an_attribute_entry_without_an_attribute_id(): void
    {
        $product = $this->makeProduct();
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'sku-1', 'price' => 10]);

        $dto = $this->makeDto($this->makeCategory()->id, [
            'variants' => [
                [
                    'id' => $variant->id,
                    'sku' => 'sku-1',
                    'price' => 10,
                    'stock' => 1,
                    'attributes' => [
                        ['attributeId' => null, 'value' => 'ignored'],
                    ],
                ],
            ],
        ]);
        $this->action->execute($product->id, $dto);

        $this->assertSame(0, ProductAttributeValue::count());
    }

    public function test_execute_skips_an_attribute_entry_with_neither_a_value_nor_a_value_id(): void
    {
        $product = $this->makeProduct();
        $variant = ProductVariant::create(['product_id' => $product->id, 'sku' => 'sku-1', 'price' => 10]);
        $attribute = Attribute::create(['code' => 'size', 'name' => ['uk' => 'Р', 'en' => 'S'], 'type' => 'text']);

        $dto = $this->makeDto($this->makeCategory()->id, [
            'variants' => [
                [
                    'id' => $variant->id,
                    'sku' => 'sku-1',
                    'price' => 10,
                    'stock' => 1,
                    'attributes' => [
                        ['attributeId' => $attribute->id, 'valueId' => null, 'value' => ''],
                    ],
                ],
            ],
        ]);
        $this->action->execute($product->id, $dto);

        $this->assertSame(0, ProductAttributeValue::count());
    }
}
