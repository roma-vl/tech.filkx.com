<?php

namespace Tests\Unit\Actions;

use App\Api\V1\Actions\GetCatalogFiltersAction;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithMeilisearch;
use Tests\TestCase;

class GetCatalogFiltersActionTest extends TestCase
{
    use InteractsWithMeilisearch, RefreshDatabase;

    private GetCatalogFiltersAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetCatalogFiltersAction::class);
        $this->flushMeilisearchIndex();
    }

    private function makeProduct(string $status = 'active'): Product
    {
        return Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => $status,
        ]);
    }

    private function makeVariant(Product $product, float $price): ProductVariant
    {
        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => $price,
        ]);
    }

    private function makeCategory(?int $parentId = null): Category
    {
        return Category::create([
            'slug' => 'cat-'.uniqid(),
            'name' => ['uk' => 'Категорія', 'en' => 'Category'],
            'order' => 0,
            'parent_id' => $parentId,
        ]);
    }

    public function test_execute_returns_the_floored_and_ceiled_price_range_of_active_products(): void
    {
        $cheap = $this->makeProduct();
        $this->makeVariant($cheap, 50.7);
        $expensive = $this->makeProduct();
        $this->makeVariant($expensive, 999.2);

        $this->reindexAllProducts();
        $result = $this->action->execute();

        $this->assertSame(50.0, $result['price']['min']);
        $this->assertSame(1000.0, $result['price']['max']);
    }

    public function test_execute_ignores_variants_belonging_to_inactive_products(): void
    {
        $active = $this->makeProduct();
        $this->makeVariant($active, 100);
        $inactive = $this->makeProduct('inactive');
        $this->makeVariant($inactive, 5);
        $this->makeVariant($inactive, 5000);

        $this->reindexAllProducts();
        $result = $this->action->execute();

        $this->assertSame(100.0, $result['price']['min']);
        $this->assertSame(100.0, $result['price']['max']);
    }

    public function test_execute_defaults_the_price_range_when_no_active_products_exist(): void
    {
        $this->reindexAllProducts();
        $result = $this->action->execute();

        $this->assertSame(0, $result['price']['min']);
        $this->assertSame(200000, $result['price']['max']);
    }

    public function test_execute_only_returns_attributes_that_have_at_least_one_value(): void
    {
        $withValues = Attribute::create(['code' => 'color', 'name' => ['uk' => 'Колір', 'en' => 'Color'], 'type' => 'select']);
        AttributeValue::create(['attribute_id' => $withValues->id, 'value' => ['uk' => 'Червоний', 'en' => 'Red']]);

        Attribute::create(['code' => 'empty-attr', 'name' => ['uk' => 'Порожній', 'en' => 'Empty'], 'type' => 'select']);

        $this->reindexAllProducts();
        $result = $this->action->execute();

        $codes = $result['attributes']->pluck('code')->all();
        $this->assertContains('color', $codes);
        $this->assertNotContains('empty-attr', $codes);
    }

    public function test_execute_maps_attribute_and_value_ids_into_the_payload(): void
    {
        $attribute = Attribute::create(['code' => 'color', 'name' => ['uk' => 'Колір', 'en' => 'Color'], 'type' => 'select']);
        $value = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => ['uk' => 'Червоний', 'en' => 'Red']]);

        $this->reindexAllProducts();
        $result = $this->action->execute();

        $attributePayload = $result['attributes']->firstWhere('code', 'color');

        $this->assertSame($attribute->id, $attributePayload['id']);
        $this->assertSame($attribute->name, $attributePayload['name']);
        $this->assertSame($attribute->type, $attributePayload['type']);
        $this->assertCount(1, $attributePayload['values']);
        $this->assertSame($value->id, $attributePayload['values']->first()['id']);
        $this->assertSame($value->value, $attributePayload['values']->first()['value']);
    }

    public function test_execute_scopes_the_price_range_to_the_given_category(): void
    {
        $category = $this->makeCategory();
        $otherCategory = $this->makeCategory();

        $inCategory = $this->makeProduct();
        $inCategory->categories()->attach($category->id);
        $this->makeVariant($inCategory, 16700);

        $outsideCategory = $this->makeProduct();
        $outsideCategory->categories()->attach($otherCategory->id);
        $this->makeVariant($outsideCategory, 99);

        $this->reindexAllProducts();
        $result = $this->action->execute($category->slug);

        $this->assertSame(16700.0, $result['price']['min']);
        $this->assertSame(16700.0, $result['price']['max']);
    }

    public function test_execute_includes_products_in_a_child_category_when_scoping_by_the_parent(): void
    {
        $parent = $this->makeCategory();
        $child = $this->makeCategory($parent->id);

        $product = $this->makeProduct();
        $product->categories()->attach($child->id);
        $this->makeVariant($product, 500);

        $this->reindexAllProducts();
        $result = $this->action->execute($parent->slug);

        $this->assertSame(500.0, $result['price']['min']);
        $this->assertSame(500.0, $result['price']['max']);
    }

    public function test_execute_only_returns_attributes_assigned_to_products_in_the_given_category(): void
    {
        $category = $this->makeCategory();
        $otherCategory = $this->makeCategory();

        $simAttribute = Attribute::create(['code' => 'sim-'.uniqid(), 'name' => ['uk' => 'SIM', 'en' => 'SIM'], 'type' => 'select']);
        $simValue = AttributeValue::create(['attribute_id' => $simAttribute->id, 'value' => ['uk' => '2', 'en' => '2']]);

        $ramAttribute = Attribute::create(['code' => 'ram-'.uniqid(), 'name' => ['uk' => 'Пам\'ять', 'en' => 'RAM'], 'type' => 'select']);
        $ramValue = AttributeValue::create(['attribute_id' => $ramAttribute->id, 'value' => ['uk' => '16GB', 'en' => '16GB']]);

        $inCategory = $this->makeProduct();
        $inCategory->categories()->attach($category->id);
        ProductAttributeValue::create([
            'product_id' => $inCategory->id,
            'attribute_id' => $ramAttribute->id,
            'attribute_value_id' => $ramValue->id,
        ]);

        $outsideCategory = $this->makeProduct();
        $outsideCategory->categories()->attach($otherCategory->id);
        ProductAttributeValue::create([
            'product_id' => $outsideCategory->id,
            'attribute_id' => $simAttribute->id,
            'attribute_value_id' => $simValue->id,
        ]);

        $this->reindexAllProducts();
        $result = $this->action->execute($category->slug);

        $codes = $result['attributes']->pluck('code')->all();
        $this->assertContains($ramAttribute->code, $codes);
        $this->assertNotContains($simAttribute->code, $codes);
    }

    public function test_execute_with_an_unknown_category_slug_returns_the_unscoped_facets(): void
    {
        $product = $this->makeProduct();
        $this->makeVariant($product, 100);

        $this->reindexAllProducts();
        $result = $this->action->execute('does-not-exist');

        $this->assertSame(100.0, $result['price']['min']);
        $this->assertSame(100.0, $result['price']['max']);
    }
}
