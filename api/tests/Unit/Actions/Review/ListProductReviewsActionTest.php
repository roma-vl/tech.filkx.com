<?php

namespace Tests\Unit\Actions\Review;

use App\Api\V1\Actions\Review\ListProductReviewsAction;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class ListProductReviewsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListProductReviewsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListProductReviewsAction::class);
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

    private function makeReview(Product $product, int $rating, string $status = 'approved'): ProductReview
    {
        return ProductReview::create([
            'product_id' => $product->id,
            'user_id' => User::factory()->create()->id,
            'rating' => $rating,
            'body' => 'A perfectly average review body.',
            'status' => $status,
        ]);
    }

    public function test_execute_throws_a_404_for_an_unknown_slug(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->action->execute('does-not-exist');
    }

    public function test_execute_only_returns_approved_reviews(): void
    {
        $product = $this->makeProduct();
        $this->makeReview($product, 5, 'approved');
        $this->makeReview($product, 1, 'pending');

        $result = $this->action->execute($product->slug);

        $this->assertCount(1, $result['reviews']);
        $this->assertSame(1, $result['stats']['count']);
    }

    public function test_execute_computes_average_and_star_distribution(): void
    {
        $product = $this->makeProduct();
        $this->makeReview($product, 5);
        $this->makeReview($product, 5);
        $this->makeReview($product, 3);

        $result = $this->action->execute($product->slug);

        $this->assertSame(3, $result['stats']['count']);
        $this->assertEquals(round((5 + 5 + 3) / 3, 1), $result['stats']['avg']);
        // distribution is ordered [5,4,3,2,1]
        $this->assertSame([2, 0, 1, 0, 0], $result['stats']['distribution']);
    }

    public function test_execute_reports_zero_stats_for_a_product_with_no_reviews(): void
    {
        $product = $this->makeProduct();

        $result = $this->action->execute($product->slug);

        $this->assertSame(0, $result['stats']['count']);
        $this->assertSame(0, $result['stats']['avg']);
    }
}
