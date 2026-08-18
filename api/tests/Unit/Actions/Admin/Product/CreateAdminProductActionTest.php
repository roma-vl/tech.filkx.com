<?php

namespace Tests\Unit\Actions\Admin\Product;

use App\Api\Admin\Actions\Product\CreateAdminProductAction;
use App\Api\Admin\Dto\ProductDto;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminProductActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateAdminProductAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateAdminProductAction::class);
    }

    private function makeCategory(): Category
    {
        return Category::create([
            'slug' => 'cat-'.uniqid(),
            'name' => ['uk' => 'A', 'en' => 'A'],
            'order' => 0,
        ]);
    }

    private function makeDto(array $overrides = []): ProductDto
    {
        return new ProductDto(
            nameUk: $overrides['nameUk'] ?? 'Товар',
            nameEn: $overrides['nameEn'] ?? 'Test Product',
            descriptionUk: $overrides['descriptionUk'] ?? null,
            descriptionEn: $overrides['descriptionEn'] ?? null,
            status: $overrides['status'] ?? 'active',
            isHot: $overrides['isHot'] ?? false,
            isRecommended: $overrides['isRecommended'] ?? false,
            brandId: $overrides['brandId'] ?? null,
            categoryId: $overrides['categoryId'] ?? $this->makeCategory()->id,
            variants: $overrides['variants'] ?? []
        );
    }

    public function test_execute_creates_a_product_with_a_slugified_name(): void
    {
        $dto = $this->makeDto(['nameEn' => 'Test Product']);

        $product = $this->action->execute($dto);

        $this->assertSame('test-product', $product->slug);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'slug' => 'test-product']);
    }

    public function test_execute_appends_a_counter_when_the_slug_already_exists(): void
    {
        Product::create([
            'slug' => 'test-product',
            'name' => ['uk' => 'Х', 'en' => 'X'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        $dto = $this->makeDto(['nameEn' => 'Test Product']);

        $product = $this->action->execute($dto);

        $this->assertSame('test-product-1', $product->slug);
    }

    public function test_execute_falls_back_to_product_when_the_name_produces_an_empty_slug(): void
    {
        $dto = $this->makeDto(['nameEn' => '']);

        $product = $this->action->execute($dto);

        $this->assertSame('product', $product->slug);
    }

    public function test_execute_syncs_the_given_category(): void
    {
        $category = $this->makeCategory();
        $dto = $this->makeDto(['categoryId' => $category->id]);

        $product = $this->action->execute($dto);

        $this->assertTrue($product->categories()->where('categories.id', $category->id)->exists());
    }

    public function test_execute_creates_a_warehouse_when_none_exists(): void
    {
        $this->assertSame(0, Warehouse::count());

        $dto = $this->makeDto();
        $this->action->execute($dto);

        $this->assertSame(1, Warehouse::count());
        $this->assertDatabaseHas('warehouses', ['name' => 'Головний склад']);
    }

    public function test_execute_reuses_an_existing_warehouse(): void
    {
        $warehouse = Warehouse::create(['name' => 'Existing', 'address' => 'Lviv']);

        $dto = $this->makeDto([
            'variants' => [
                ['sku' => 'sku-1', 'price' => 100, 'stock' => 5],
            ],
        ]);
        $this->action->execute($dto);

        $this->assertSame(1, Warehouse::count());
        $this->assertDatabaseHas('stocks', ['warehouse_id' => $warehouse->id, 'quantity' => 5]);
    }

    public function test_execute_creates_variants_with_stock(): void
    {
        $dto = $this->makeDto([
            'variants' => [
                ['sku' => 'sku-1', 'price' => 150, 'oldPrice' => 200, 'weight' => 1.5, 'stock' => 10, 'images' => ['img.jpg']],
            ],
        ]);

        $product = $this->action->execute($dto);

        $variant = $product->variants()->first();
        $this->assertNotNull($variant);
        $this->assertSame('sku-1', $variant->sku);
        $this->assertSame('200.00', $variant->old_price);
        $this->assertSame(['images' => ['img.jpg']], $variant->dimensions);
        $this->assertDatabaseHas('stocks', ['variant_id' => $variant->id, 'quantity' => 10, 'reserved' => 0]);
    }

    public function test_execute_creates_attribute_values_for_a_variant(): void
    {
        $attribute = Attribute::create(['code' => 'color', 'name' => ['uk' => 'Колір', 'en' => 'Color'], 'type' => 'color']);
        $attributeValue = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => ['value' => '#fff']]);

        $dto = $this->makeDto([
            'variants' => [
                [
                    'sku' => 'sku-1',
                    'price' => 100,
                    'stock' => 1,
                    'attributes' => [
                        ['attributeId' => $attribute->id, 'valueId' => $attributeValue->id],
                    ],
                ],
            ],
        ]);

        $product = $this->action->execute($dto);

        $this->assertDatabaseHas('product_attribute_values', [
            'product_id' => $product->id,
            'attribute_id' => $attribute->id,
            'attribute_value_id' => $attributeValue->id,
        ]);
    }

    public function test_execute_skips_an_attribute_entry_without_an_attribute_id(): void
    {
        $dto = $this->makeDto([
            'variants' => [
                [
                    'sku' => 'sku-1',
                    'price' => 100,
                    'stock' => 1,
                    'attributes' => [
                        ['attributeId' => null, 'value' => 'ignored'],
                    ],
                ],
            ],
        ]);

        $this->action->execute($dto);

        $this->assertSame(0, ProductAttributeValue::count());
    }

    public function test_execute_skips_an_attribute_entry_with_neither_a_value_nor_a_value_id(): void
    {
        $attribute = Attribute::create(['code' => 'size', 'name' => ['uk' => 'Розмір', 'en' => 'Size'], 'type' => 'text']);

        $dto = $this->makeDto([
            'variants' => [
                [
                    'sku' => 'sku-1',
                    'price' => 100,
                    'stock' => 1,
                    'attributes' => [
                        ['attributeId' => $attribute->id, 'valueId' => null, 'value' => ''],
                    ],
                ],
            ],
        ]);

        $this->action->execute($dto);

        $this->assertSame(0, ProductAttributeValue::count());
    }

    public function test_execute_stores_a_custom_attribute_value(): void
    {
        $attribute = Attribute::create(['code' => 'size', 'name' => ['uk' => 'Розмір', 'en' => 'Size'], 'type' => 'text']);

        $dto = $this->makeDto([
            'variants' => [
                [
                    'sku' => 'sku-1',
                    'price' => 100,
                    'stock' => 1,
                    'attributes' => [
                        ['attributeId' => $attribute->id, 'valueId' => null, 'value' => 'XL'],
                    ],
                ],
            ],
        ]);

        $product = $this->action->execute($dto);

        $this->assertDatabaseHas('product_attribute_values', [
            'product_id' => $product->id,
            'attribute_id' => $attribute->id,
            'custom_value' => 'XL',
        ]);
    }
}
