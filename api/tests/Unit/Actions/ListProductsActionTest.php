<?php

namespace Tests\Unit\Actions;

use App\Api\V1\Actions\ListProductsAction;
use App\Models\Attribute;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ListProductsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListProductsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        // Filters exercised here never use the `search` keyword, so Scout/Meilisearch is
        // never reached; the driver is still nulled to keep product creation off the network,
        // mirroring api/tests/Feature/Catalog/CatalogControllerTest.php.
        config(['scout.driver' => 'null']);

        $this->action = app(ListProductsAction::class);
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

    private function attachProductAttribute(Product $product, Attribute $attribute, string $customValue, ?int $variantId = null): ProductAttributeValue
    {
        return ProductAttributeValue::create([
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'attribute_id' => $attribute->id,
            'custom_value' => $customValue,
        ]);
    }

    public function test_execute_filters_by_a_product_level_attribute_custom_value(): void
    {
        $attribute = Attribute::create(['code' => 'color', 'name' => ['uk' => 'Колір', 'en' => 'Color'], 'type' => 'text']);

        $matching = $this->makeProduct();
        $this->makeVariant($matching, 100);
        $this->attachProductAttribute($matching, $attribute, 'red');

        $nonMatching = $this->makeProduct();
        $this->makeVariant($nonMatching, 100);
        $this->attachProductAttribute($nonMatching, $attribute, 'blue');

        $result = $this->action->execute(['attrs' => ['color' => 'red']]);

        $slugs = collect($result->items())->pluck('slug')->all();
        $this->assertContains($matching->slug, $slugs);
        $this->assertNotContains($nonMatching->slug, $slugs);
    }

    public function test_execute_filters_by_a_variant_level_attribute_custom_value(): void
    {
        $attribute = Attribute::create(['code' => 'ram', 'name' => ['uk' => 'Пам\'ять', 'en' => 'RAM'], 'type' => 'text']);

        $matching = $this->makeProduct();
        $matchingVariant = $this->makeVariant($matching, 100);
        $this->attachProductAttribute($matching, $attribute, '8GB', $matchingVariant->id);

        $nonMatching = $this->makeProduct();
        $nonMatchingVariant = $this->makeVariant($nonMatching, 100);
        $this->attachProductAttribute($nonMatching, $attribute, '16GB', $nonMatchingVariant->id);

        $result = $this->action->execute(['attrs' => ['ram' => '8GB']]);

        $slugs = collect($result->items())->pluck('slug')->all();
        $this->assertContains($matching->slug, $slugs);
        $this->assertNotContains($nonMatching->slug, $slugs);
    }

    public function test_execute_accepts_comma_separated_attribute_values_as_a_string(): void
    {
        $attribute = Attribute::create(['code' => 'color', 'name' => ['uk' => 'Колір', 'en' => 'Color'], 'type' => 'text']);

        $green = $this->makeProduct();
        $this->makeVariant($green, 100);
        $this->attachProductAttribute($green, $attribute, 'green');

        $result = $this->action->execute(['attrs' => ['color' => 'red,green']]);

        $slugs = collect($result->items())->pluck('slug')->all();
        $this->assertContains($green->slug, $slugs);
    }

    public function test_execute_skips_an_attribute_filter_with_no_values(): void
    {
        $product = $this->makeProduct();
        $this->makeVariant($product, 100);

        $result = $this->action->execute(['attrs' => ['color' => []]]);

        $slugs = collect($result->items())->pluck('slug')->all();
        $this->assertContains($product->slug, $slugs);
    }

    public function test_execute_sorts_by_newest_first(): void
    {
        $older = $this->makeProduct();
        $this->makeVariant($older, 100);
        DB::table('products')->where('id', $older->id)->update(['created_at' => now()->subDay()]);

        $newer = $this->makeProduct();
        $this->makeVariant($newer, 100);

        $result = $this->action->execute(['sort_by' => 'newest']);

        $slugs = collect($result->items())->pluck('slug')->all();
        $this->assertSame($newer->slug, $slugs[0]);
    }

    public function test_execute_sorts_by_price_ascending(): void
    {
        $expensive = $this->makeProduct();
        $this->makeVariant($expensive, 300);

        $cheap = $this->makeProduct();
        $this->makeVariant($cheap, 100);

        $result = $this->action->execute(['sort_by' => 'price-asc']);

        $slugs = collect($result->items())->pluck('slug')->all();
        $this->assertSame($cheap->slug, $slugs[0]);
    }

    public function test_execute_sorts_by_price_descending(): void
    {
        $cheap = $this->makeProduct();
        $this->makeVariant($cheap, 100);

        $expensive = $this->makeProduct();
        $this->makeVariant($expensive, 300);

        $result = $this->action->execute(['sort_by' => 'price-desc']);

        $slugs = collect($result->items())->pluck('slug')->all();
        $this->assertSame($expensive->slug, $slugs[0]);
    }
}
