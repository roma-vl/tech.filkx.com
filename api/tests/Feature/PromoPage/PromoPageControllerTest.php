<?php

namespace Tests\Feature\PromoPage;

use App\Models\Product;
use App\Models\PromoPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromoPageControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ], $overrides));
    }

    public function test_show_returns_an_active_promo_page_with_its_products(): void
    {
        $promoPage = PromoPage::factory()->create(['slug' => 'back-to-school', 'title' => 'Все для школи']);
        $activeProduct = $this->makeProduct();
        $inactiveProduct = $this->makeProduct(['status' => 'draft']);

        $promoPage->products()->sync([
            $activeProduct->id => ['sort_order' => 0],
            $inactiveProduct->id => ['sort_order' => 1],
        ]);

        $response = $this->getJson('/api/v1/promo/back-to-school');

        $response->assertOk()
            ->assertJsonPath('data.slug', 'back-to-school')
            ->assertJsonPath('data.title', 'Все для школи');

        $productIds = collect($response->json('data.products'))->pluck('id');

        // Only the active product is included, even though both are attached.
        $this->assertCount(1, $productIds);
        $this->assertContains($activeProduct->id, $productIds);
    }

    public function test_show_returns_404_for_an_unknown_slug(): void
    {
        $this->getJson('/api/v1/promo/does-not-exist')->assertNotFound();
    }

    public function test_show_returns_404_for_an_inactive_promo_page(): void
    {
        PromoPage::factory()->create(['slug' => 'hidden-promo', 'is_active' => false]);

        $this->getJson('/api/v1/promo/hidden-promo')->assertNotFound();
    }
}
