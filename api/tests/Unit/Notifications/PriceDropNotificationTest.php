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

    public function test_it_renders_the_subject_and_database_content_in_the_recipients_locale(): void
    {
        $product = Product::create([
            'slug' => 'test-product',
            'name' => ['uk' => 'Навушники', 'en' => 'Headphones'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        $ukUser = User::factory()->create(['locale' => 'uk']);
        $enUser = User::factory()->create(['locale' => 'en']);

        $notification = new PriceDropNotification($product, 1000.0, 800.0);

        $ukMail = $notification->toMail($ukUser);
        $enMail = $notification->toMail($enUser);

        $this->assertStringContainsString('Знижка', $ukMail->subject);
        $this->assertStringContainsString('Навушники', $ukMail->subject);
        $this->assertStringContainsString('off', $enMail->subject);
        $this->assertStringContainsString('Headphones', $enMail->subject);

        $ukDb = $notification->toDatabase($ukUser);
        $enDb = $notification->toDatabase($enUser);

        $this->assertStringContainsString('Ціна знизилась', $ukDb['content']);
        $this->assertStringContainsString('Price dropped', $enDb['content']);
    }

    public function test_it_renders_ukrainian_body_html_for_a_uk_locale_user(): void
    {
        $product = Product::create([
            'slug' => 'test-product',
            'name' => ['uk' => 'Навушники', 'en' => 'Headphones'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        $user = User::factory()->create(['locale' => 'uk']);

        $html = (new PriceDropNotification($product, 1000.0, 800.0))->toMail($user)->render();

        $this->assertStringContainsString('Ваша економія', $html);
        $this->assertStringNotContainsString('Your savings', $html);
    }

    public function test_it_renders_english_body_html_for_an_en_locale_user(): void
    {
        $product = Product::create([
            'slug' => 'test-product',
            'name' => ['uk' => 'Навушники', 'en' => 'Headphones'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);

        $user = User::factory()->create(['locale' => 'en']);

        $html = (new PriceDropNotification($product, 1000.0, 800.0))->toMail($user)->render();

        $this->assertStringContainsString('Your savings', $html);
        $this->assertStringNotContainsString('Ваша економія', $html);
    }
}
