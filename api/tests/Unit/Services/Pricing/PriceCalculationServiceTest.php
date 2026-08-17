<?php

namespace Tests\Unit\Services\Pricing;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Promotion;
use App\Services\Pricing\Dto\CartLineItemDto;
use App\Services\Pricing\PriceCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private PriceCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PriceCalculationService::class);
    }

    private function makeCategory(string $name = 'Category'): Category
    {
        return Category::create([
            'slug' => strtolower($name).'-'.uniqid(),
            'name' => ['uk' => $name, 'en' => $name],
            'order' => 0,
        ]);
    }

    private function makeProduct(string $name = 'Product'): Product
    {
        return Product::create([
            'slug' => strtolower($name).'-'.uniqid(),
            'name' => ['uk' => $name, 'en' => $name],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
    }

    private function makeCoupon(string $type, float $amount): Coupon
    {
        return Coupon::create([
            'code' => 'TEST'.uniqid(),
            'type' => $type,
            'amount' => $amount,
            'is_active' => true,
        ]);
    }

    private function makePromotion(string $type, float $amount): Promotion
    {
        return Promotion::create([
            'name' => 'Promo '.uniqid(),
            'type' => $type,
            'amount' => $amount,
            'is_active' => true,
        ]);
    }

    public function test_untargeted_coupon_applies_to_the_entire_cart(): void
    {
        $coupon = $this->makeCoupon('percent', 10);

        $items = [
            new CartLineItemDto(productId: 1, categoryIds: [], unitPrice: 100, quantity: 2),
            new CartLineItemDto(productId: 2, categoryIds: [], unitPrice: 50, quantity: 1),
        ];

        $result = $this->service->calculate($items, $coupon);

        $this->assertSame(250.0, $result->subtotal);
        $this->assertSame(25.0, $result->couponDiscount);
        $this->assertSame(0.0, $result->promotionDiscount);
        $this->assertSame(225.0, $result->total);
    }

    public function test_category_targeted_coupon_only_discounts_matching_items(): void
    {
        $targetedCategory = $this->makeCategory('Laptops');
        $otherCategory = $this->makeCategory('Phones');

        $coupon = $this->makeCoupon('percent', 10);
        $coupon->categories()->attach($targetedCategory->id);

        $items = [
            // Matches the coupon's category.
            new CartLineItemDto(productId: 1, categoryIds: [$targetedCategory->id], unitPrice: 100, quantity: 1),
            // Does not match.
            new CartLineItemDto(productId: 2, categoryIds: [$otherCategory->id], unitPrice: 200, quantity: 1),
        ];

        $result = $this->service->calculate($items, $coupon->fresh(['categories', 'products']));

        $this->assertSame(300.0, $result->subtotal);
        // Only the 100 (targeted) item is discounted, not the 200 (untargeted) item.
        $this->assertSame(10.0, $result->couponDiscount);
        $this->assertSame(290.0, $result->total);
    }

    public function test_product_targeted_coupon_only_discounts_matching_product(): void
    {
        $product = $this->makeProduct('Targeted Product');

        $coupon = $this->makeCoupon('fixed', 30);
        $coupon->products()->attach($product->id);

        $items = [
            new CartLineItemDto(productId: $product->id, categoryIds: [], unitPrice: 100, quantity: 1),
            new CartLineItemDto(productId: 999, categoryIds: [], unitPrice: 100, quantity: 1),
        ];

        $result = $this->service->calculate($items, $coupon->fresh(['categories', 'products']));

        $this->assertSame(30.0, $result->couponDiscount);
        $this->assertSame(170.0, $result->total);
    }

    public function test_fixed_discount_is_capped_and_prorated_across_matched_items(): void
    {
        $category = $this->makeCategory('Sale Category');

        $coupon = $this->makeCoupon('fixed', 500); // larger than the matched subtotal
        $coupon->categories()->attach($category->id);

        $items = [
            new CartLineItemDto(productId: 1, categoryIds: [$category->id], unitPrice: 60, quantity: 1),
            new CartLineItemDto(productId: 2, categoryIds: [$category->id], unitPrice: 40, quantity: 1),
        ];

        $result = $this->service->calculate($items, $coupon->fresh(['categories', 'products']));

        // Matched subtotal is only 100, so the discount is capped there, not 500.
        $this->assertSame(100.0, $result->couponDiscount);
        $this->assertSame(0.0, $result->total);
    }

    public function test_promotion_applies_automatically_without_a_coupon(): void
    {
        $promotion = $this->makePromotion('percent', 15);

        $items = [
            new CartLineItemDto(productId: 1, categoryIds: [], unitPrice: 200, quantity: 1),
        ];

        $result = $this->service->calculate($items);

        $this->assertSame(30.0, $result->promotionDiscount);
        $this->assertSame(0.0, $result->couponDiscount);
        $this->assertSame(170.0, $result->total);
        $this->assertSame([$promotion->id], $result->appliedPromotionIds);
    }

    public function test_coupon_and_non_overlapping_promotion_stack(): void
    {
        $couponCategory = $this->makeCategory('Coupon Category');
        $promoCategory = $this->makeCategory('Promo Category');

        $coupon = $this->makeCoupon('percent', 10);
        $coupon->categories()->attach($couponCategory->id);

        $this->makePromotion('percent', 20)->categories()->attach($promoCategory->id);

        $items = [
            new CartLineItemDto(productId: 1, categoryIds: [$couponCategory->id], unitPrice: 100, quantity: 1),
            new CartLineItemDto(productId: 2, categoryIds: [$promoCategory->id], unitPrice: 100, quantity: 1),
        ];

        $result = $this->service->calculate($items, $coupon->fresh(['categories', 'products']));

        $this->assertSame(10.0, $result->couponDiscount);
        $this->assertSame(20.0, $result->promotionDiscount);
        $this->assertSame(30.0, $result->totalDiscount);
        $this->assertSame(170.0, $result->total);
    }

    public function test_overlapping_coupon_and_promotion_do_not_stack_the_larger_wins(): void
    {
        $category = $this->makeCategory('Shared Category');

        $coupon = $this->makeCoupon('percent', 10);
        $coupon->categories()->attach($category->id);

        $promotion = $this->makePromotion('percent', 25);
        $promotion->categories()->attach($category->id);

        $items = [
            new CartLineItemDto(productId: 1, categoryIds: [$category->id], unitPrice: 100, quantity: 1),
        ];

        $result = $this->service->calculate($items, $coupon->fresh(['categories', 'products']));

        // The promotion (25%) beats the coupon (10%) on this item, so only the
        // promotion's discount is applied — never both on the same unit.
        $this->assertSame(0.0, $result->couponDiscount);
        $this->assertSame(25.0, $result->promotionDiscount);
        $this->assertSame(25.0, $result->totalDiscount);
        $this->assertSame(75.0, $result->total);
    }

    public function test_empty_cart_has_no_discount(): void
    {
        $coupon = $this->makeCoupon('percent', 10);

        $result = $this->service->calculate([], $coupon);

        $this->assertSame(0.0, $result->subtotal);
        $this->assertSame(0.0, $result->totalDiscount);
    }
}
