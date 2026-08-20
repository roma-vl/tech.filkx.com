<?php

namespace Tests\Unit\Actions\Coupon;

use App\Api\V1\Actions\Coupon\ValidateCouponAction;
use App\Api\V1\Dto\CartSessionDto;
use App\Api\V1\Dto\ValidateCouponDto;
use App\Api\V1\Exceptions\CheckoutValidationException;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ValidateCouponActionTest extends TestCase
{
    use RefreshDatabase;

    private ValidateCouponAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ValidateCouponAction::class);
    }

    private function makeCartWithVariant(float $price, ?Category $category = null): Cart
    {
        $product = Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        if ($category) {
            $product->categories()->attach($category->id);
        }

        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => $price,
        ]);

        $warehouse = Warehouse::create(['name' => 'Main']);
        Stock::create([
            'variant_id' => $variant->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'reserved' => 0,
        ]);

        $cart = Cart::create(['session_id' => 'session-'.uniqid()]);
        CartItem::create([
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => 1,
        ]);

        return $cart;
    }

    private function dtoFor(string $code, Cart $cart): ValidateCouponDto
    {
        return new ValidateCouponDto(
            code: $code,
            cartSession: new CartSessionDto(userId: null, sessionId: $cart->session_id)
        );
    }

    public function test_inactive_coupon_is_rejected(): void
    {
        $cart = $this->makeCartWithVariant(100);
        Coupon::create(['code' => 'INACTIVE', 'type' => 'percent', 'amount' => 10, 'is_active' => false]);

        $this->expectException(CheckoutValidationException::class);

        $this->action->execute($this->dtoFor('INACTIVE', $cart));
    }

    public function test_expired_coupon_is_rejected(): void
    {
        $cart = $this->makeCartWithVariant(100);
        Coupon::create([
            'code' => 'EXPIRED',
            'type' => 'percent',
            'amount' => 10,
            'is_active' => true,
            'expires_at' => now()->subDay(),
        ]);

        $this->expectException(CheckoutValidationException::class);

        $this->action->execute($this->dtoFor('EXPIRED', $cart));
    }

    public function test_usage_exhausted_coupon_is_rejected(): void
    {
        $cart = $this->makeCartWithVariant(100);
        Coupon::create([
            'code' => 'MAXED',
            'type' => 'percent',
            'amount' => 10,
            'is_active' => true,
            'usage_limit' => 5,
            'used_count' => 5,
        ]);

        $this->expectException(CheckoutValidationException::class);

        $this->action->execute($this->dtoFor('MAXED', $cart));
    }

    public function test_unknown_coupon_code_is_rejected(): void
    {
        $cart = $this->makeCartWithVariant(100);

        $this->expectException(CheckoutValidationException::class);

        $this->action->execute($this->dtoFor('DOESNOTEXIST', $cart));
    }

    public function test_empty_cart_is_rejected(): void
    {
        Coupon::create(['code' => 'VALID10', 'type' => 'percent', 'amount' => 10, 'is_active' => true]);
        $cart = Cart::create(['session_id' => 'empty-'.uniqid()]);

        $this->expectException(CheckoutValidationException::class);

        $this->action->execute($this->dtoFor('VALID10', $cart));
    }

    public function test_valid_coupon_discounts_the_real_server_side_cart_total(): void
    {
        $cart = $this->makeCartWithVariant(200);
        Coupon::create(['code' => 'VALID10', 'type' => 'percent', 'amount' => 10, 'is_active' => true]);

        $result = $this->action->execute($this->dtoFor('VALID10', $cart));

        $this->assertSame('VALID10', $result->code);
        $this->assertSame(20.0, $result->discount);
    }

    public function test_category_targeted_coupon_ignores_the_client_supplied_code_case(): void
    {
        $category = Category::create([
            'slug' => 'targeted-'.uniqid(),
            'name' => ['uk' => 'Категорія', 'en' => 'Category'],
        ]);

        $cart = $this->makeCartWithVariant(200, $category);

        $coupon = Coupon::create(['code' => 'CATONLY', 'type' => 'percent', 'amount' => 50, 'is_active' => true]);
        $coupon->categories()->attach($category->id);

        $result = $this->action->execute($this->dtoFor('catonly', $cart));

        $this->assertSame(100.0, $result->discount);
    }
}
