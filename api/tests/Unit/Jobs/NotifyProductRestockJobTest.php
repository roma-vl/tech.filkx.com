<?php

namespace Tests\Unit\Jobs;

use App\Jobs\NotifyProductRestockJob;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Notifications\BackInStockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NotifyProductRestockJobTest extends TestCase
{
    use RefreshDatabase;

    private function makeProduct(): Product
    {
        return Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
    }

    public function test_handle_notifies_subscribed_users_and_clears_the_flag(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->favorites()->attach($product->id, ['notify_on_restock' => true]);

        (new NotifyProductRestockJob($product->id))->handle();

        Notification::assertSentTo($user, BackInStockNotification::class);
        $pivot = Wishlist::where('user_id', $user->id)->where('product_id', $product->id)->firstOrFail();
        $this->assertFalse($pivot->notify_on_restock, 'is a one-shot notification, must not fire again on the next restock');
    }

    public function test_handle_skips_users_who_never_subscribed(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->favorites()->attach($product->id, ['notify_on_restock' => false]);

        (new NotifyProductRestockJob($product->id))->handle();

        Notification::assertNothingSent();
    }

    public function test_handle_is_a_no_op_for_a_deleted_product(): void
    {
        Notification::fake();

        (new NotifyProductRestockJob(999999))->handle();

        Notification::assertNothingSent();
    }
}
