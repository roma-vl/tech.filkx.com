<?php

namespace Tests\Unit\Actions\Cart;

use App\Api\V1\Actions\Cart\SendAbandonedCartRemindersAction;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\AbandonedCartReminderNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendAbandonedCartRemindersActionTest extends TestCase
{
    use RefreshDatabase;

    private function makeVariant(float $price = 100): ProductVariant
    {
        $product = Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        return ProductVariant::create([
            'product_id' => $product->id,
            'sku' => 'sku-'.uniqid(),
            'price' => $price,
        ]);
    }

    private function makeCart(array $attributes = []): Cart
    {
        return Cart::create(array_merge([
            'session_id' => 'session-'.uniqid(),
        ], $attributes));
    }

    private function addItem(Cart $cart, ProductVariant $variant, int $quantity = 1): CartItem
    {
        return CartItem::create([
            'cart_id' => $cart->id,
            'variant_id' => $variant->id,
            'quantity' => $quantity,
        ]);
    }

    private function ageCart(Cart $cart, int $hoursOld): void
    {
        $cart->timestamps = false;
        $cart->updated_at = now()->subHours($hoursOld);
        $cart->save();
    }

    public function test_it_reminds_a_user_cart_abandoned_past_the_threshold(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $cart = $this->makeCart(['user_id' => $user->id, 'session_id' => null]);
        $this->addItem($cart, $this->makeVariant());
        $this->ageCart($cart, 6);

        $sent = app(SendAbandonedCartRemindersAction::class)->execute();

        $this->assertSame(1, $sent);
        $this->assertDatabaseHas('carts', ['id' => $cart->id]);
        $this->assertNotNull($cart->fresh()->reminder_sent_at);
        Notification::assertSentTo(
            $user,
            AbandonedCartReminderNotification::class,
            fn (AbandonedCartReminderNotification $notification) => $notification->cart->is($cart)
        );
    }

    public function test_it_ignores_a_cart_that_is_not_old_enough_yet(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $cart = $this->makeCart(['user_id' => $user->id, 'session_id' => null]);
        $this->addItem($cart, $this->makeVariant());
        $this->ageCart($cart, 1);

        $sent = app(SendAbandonedCartRemindersAction::class)->execute();

        $this->assertSame(0, $sent);
        $this->assertNull($cart->fresh()->reminder_sent_at);
        Notification::assertNothingSent();
    }

    public function test_it_ignores_a_cart_with_no_items(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $cart = $this->makeCart(['user_id' => $user->id, 'session_id' => null]);
        $this->ageCart($cart, 10);

        $sent = app(SendAbandonedCartRemindersAction::class)->execute();

        $this->assertSame(0, $sent);
        Notification::assertNothingSent();
    }

    public function test_it_ignores_a_checked_out_cart_whose_items_were_cleared(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $cart = $this->makeCart(['user_id' => $user->id, 'session_id' => null]);
        $item = $this->addItem($cart, $this->makeVariant());
        $this->ageCart($cart, 10);
        $item->delete();

        $sent = app(SendAbandonedCartRemindersAction::class)->execute();

        $this->assertSame(0, $sent);
        Notification::assertNothingSent();
    }

    public function test_it_ignores_a_guest_cart_with_no_registered_user(): void
    {
        Notification::fake();
        $cart = $this->makeCart();
        $this->addItem($cart, $this->makeVariant());
        $this->ageCart($cart, 10);

        $sent = app(SendAbandonedCartRemindersAction::class)->execute();

        $this->assertSame(0, $sent);
        Notification::assertNothingSent();
    }

    public function test_it_ignores_a_long_lived_cart_that_just_received_a_new_item(): void
    {
        // Regression test for CartItem::$touches: adding an item must refresh
        // Cart::updated_at, otherwise a returning customer's old-but-empty
        // cart row (created long before the reminder threshold) would look
        // "abandoned" the instant they add something to it.
        Notification::fake();
        $user = User::factory()->create();
        $cart = $this->makeCart(['user_id' => $user->id, 'session_id' => null]);
        $this->ageCart($cart, 240);

        $this->addItem($cart, $this->makeVariant());

        $sent = app(SendAbandonedCartRemindersAction::class)->execute();

        $this->assertSame(0, $sent);
        $this->assertNull($cart->fresh()->reminder_sent_at);
        Notification::assertNothingSent();
    }

    public function test_it_does_not_remind_a_cart_twice(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $cart = $this->makeCart(['user_id' => $user->id, 'session_id' => null]);
        $this->addItem($cart, $this->makeVariant());
        $this->ageCart($cart, 10);
        $cart->update(['reminder_sent_at' => now()->subHour()]);

        $sent = app(SendAbandonedCartRemindersAction::class)->execute();

        $this->assertSame(0, $sent);
        Notification::assertNothingSent();
    }
}
