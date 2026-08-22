<?php

namespace Tests\Unit\Notifications;

use App\Models\Product;
use App\Models\User;
use App\Notifications\PriceDropNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriceDropNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_database_and_mail_links_point_at_the_real_product_detail_route(): void
    {
        // Regression test: both links previously used '/products/{slug}' (plural),
        // but the frontend's actual route is 'product/:id' (singular) - "Детальніше"
        // was leading nowhere real.
        $user = User::factory()->create();
        $product = Product::create([
            'slug' => 'test-product',
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        $notification = new PriceDropNotification($product, 1000.0, 800.0);

        $this->assertSame('/product/test-product', $notification->toDatabase($user)['link']);
        $this->assertStringContainsString(
            '/product/test-product',
            $notification->toMail($user)->viewData['productUrl'],
        );
    }
}
