<?php

namespace Tests\Unit\Actions\Review;

use App\Api\V1\Actions\Review\ListMyReviewsAction;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListMyReviewsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListMyReviewsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListMyReviewsAction::class);
    }

    private function makeProduct(): Product
    {
        return Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
    }

    public function test_execute_only_returns_reviews_belonging_to_the_user(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 4,
            'body' => 'My own review body text.',
            'status' => 'approved',
        ]);
        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $otherUser->id,
            'rating' => 2,
            'body' => "Someone else's review body.",
            'status' => 'approved',
        ]);

        $result = $this->action->execute($user);

        $this->assertCount(1, $result);
        $this->assertSame($product->slug, $result->first()['product_slug']);
    }

    public function test_execute_returns_an_empty_collection_when_the_user_has_no_reviews(): void
    {
        $result = $this->action->execute(User::factory()->create());

        $this->assertCount(0, $result);
    }
}
