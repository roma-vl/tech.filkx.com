<?php

namespace Tests\Unit\Actions;

use App\Api\V1\Actions\GetCatalogFiltersAction;
use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetCatalogFiltersActionTest extends TestCase
{
    use RefreshDatabase;

    private GetCatalogFiltersAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetCatalogFiltersAction::class);
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

    public function test_execute_returns_the_floored_and_ceiled_price_range_of_active_products(): void
    {
        $cheap = $this->makeProduct();
        $this->makeVariant($cheap, 50.7);
        $expensive = $this->makeProduct();
        $this->makeVariant($expensive, 999.2);

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

        $result = $this->action->execute();

        $this->assertSame(100.0, $result['price']['min']);
        $this->assertSame(100.0, $result['price']['max']);
    }

    public function test_execute_defaults_the_price_range_when_no_active_products_exist(): void
    {
        $result = $this->action->execute();

        $this->assertSame(0, $result['price']['min']);
        $this->assertSame(200000, $result['price']['max']);
    }

    public function test_execute_only_returns_attributes_that_have_at_least_one_value(): void
    {
        $withValues = Attribute::create(['code' => 'color', 'name' => ['uk' => 'Колір', 'en' => 'Color'], 'type' => 'select']);
        AttributeValue::create(['attribute_id' => $withValues->id, 'value' => ['uk' => 'Червоний', 'en' => 'Red']]);

        Attribute::create(['code' => 'empty-attr', 'name' => ['uk' => 'Порожній', 'en' => 'Empty'], 'type' => 'select']);

        $result = $this->action->execute();

        $codes = $result['attributes']->pluck('code')->all();
        $this->assertContains('color', $codes);
        $this->assertNotContains('empty-attr', $codes);
    }

    public function test_execute_maps_attribute_and_value_ids_into_the_payload(): void
    {
        $attribute = Attribute::create(['code' => 'color', 'name' => ['uk' => 'Колір', 'en' => 'Color'], 'type' => 'select']);
        $value = AttributeValue::create(['attribute_id' => $attribute->id, 'value' => ['uk' => 'Червоний', 'en' => 'Red']]);

        $result = $this->action->execute();

        $attributePayload = $result['attributes']->firstWhere('code', 'color');

        $this->assertSame($attribute->id, $attributePayload['id']);
        $this->assertSame($attribute->name, $attributePayload['name']);
        $this->assertSame($attribute->type, $attributePayload['type']);
        $this->assertCount(1, $attributePayload['values']);
        $this->assertSame($value->id, $attributePayload['values']->first()['id']);
        $this->assertSame($value->value, $attributePayload['values']->first()['value']);
    }
}
