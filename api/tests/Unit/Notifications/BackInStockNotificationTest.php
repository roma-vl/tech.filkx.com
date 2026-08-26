<?php

namespace Tests\Unit\Notifications;

use App\Models\Product;
use App\Models\User;
use App\Notifications\BackInStockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BackInStockNotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        return Product::create([
            'slug' => 'test-product',
            'name' => ['uk' => 'Навушники', 'en' => 'Headphones'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
    }

    public function test_it_renders_the_subject_and_database_content_in_the_recipients_locale(): void
    {
        $product = $this->makeProduct();
        $ukUser = User::factory()->create(['locale' => 'uk']);
        $enUser = User::factory()->create(['locale' => 'en']);

        $notification = new BackInStockNotification($product);

        $this->assertStringContainsString('Навушники', $notification->toMail($ukUser)->subject);
        $this->assertStringContainsString('Headphones', $notification->toMail($enUser)->subject);

        $this->assertStringContainsString('знову можна замовити', $notification->toDatabase($ukUser)['content']);
        $this->assertStringContainsString('available to order again', $notification->toDatabase($enUser)['content']);
    }

    public function test_it_defaults_to_ukrainian_when_the_notifiable_has_no_locale_property(): void
    {
        $product = $this->makeProduct();

        $notifiable = new class
        {
            public string $name = 'Guest';
        };

        $mail = (new BackInStockNotification($product))->toMail($notifiable);

        $this->assertStringContainsString('Знову в наявності', $mail->subject);
    }

    public function test_it_renders_ukrainian_body_html_for_a_uk_locale_user(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create(['locale' => 'uk']);

        $html = (new BackInStockNotification($product))->toMail($user)->render();

        $this->assertStringContainsString('знову в наявності', $html);
        $this->assertStringNotContainsString('back in stock', $html);
    }

    public function test_it_renders_english_body_html_for_an_en_locale_user(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create(['locale' => 'en']);

        $html = (new BackInStockNotification($product))->toMail($user)->render();

        $this->assertStringContainsString('back in stock', $html);
        $this->assertStringNotContainsString('знову в наявності', $html);
    }
}
