<?php

namespace Tests\Unit\Notifications;

use App\Models\Order;
use App\Models\User;
use App\Notifications\OrderConfirmedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderConfirmedNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeOrder(?User $user): Order
    {
        return Order::create([
            'order_number' => 'ORD-'.uniqid(),
            'user_id' => $user?->id,
            'customer_name' => 'Roma',
            'customer_email' => 'roma@example.com',
            'customer_phone' => '+380000000000',
            'shipping_address' => 'Street 1',
            'delivery_method' => 'courier',
            'payment_method' => 'card',
            'total_price' => 1000,
        ]);
    }

    public function test_it_renders_in_the_locale_of_the_orders_registered_user(): void
    {
        $enUser = User::factory()->create(['locale' => 'en']);
        $order = $this->makeOrder($enUser);

        $mail = (new OrderConfirmedNotification($order))->toMail($order->user);

        $this->assertStringContainsString('received', $mail->subject);
        $this->assertStringContainsString($order->order_number, $mail->subject);
    }

    public function test_it_falls_back_to_ukrainian_for_a_guest_order_with_no_user(): void
    {
        $order = $this->makeOrder(null);

        // Guest checkout notifies via Notification::route('mail', $order->customer_email),
        // an anonymous notifiable with no locale property of its own.
        $anonymous = new \Illuminate\Notifications\AnonymousNotifiable;

        $mail = (new OrderConfirmedNotification($order))->toMail($anonymous);

        $this->assertStringContainsString('прийнято', $mail->subject);
    }

    public function test_it_renders_ukrainian_for_a_uk_locale_users_order(): void
    {
        $ukUser = User::factory()->create(['locale' => 'uk']);
        $order = $this->makeOrder($ukUser);

        $html = (new OrderConfirmedNotification($order))->toMail($order->user)->render();

        $this->assertStringContainsString('Дякуємо за замовлення', $html);
        $this->assertStringNotContainsString('Thank you for your order', $html);
    }

    public function test_it_renders_english_for_an_en_locale_users_order(): void
    {
        $enUser = User::factory()->create(['locale' => 'en']);
        $order = $this->makeOrder($enUser);

        $html = (new OrderConfirmedNotification($order))->toMail($order->user)->render();

        $this->assertStringContainsString('Thank you for your order', $html);
        $this->assertStringNotContainsString('Дякуємо за замовлення', $html);
    }
}
