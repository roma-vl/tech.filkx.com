<?php

namespace Tests\Unit\Actions\Review;

use App\Api\V1\Actions\Review\UpdateProductReviewAction;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

class UpdateProductReviewActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateProductReviewAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateProductReviewAction::class);
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

    private function makeReview(Product $product, User $user, ?array $photos = null): ProductReview
    {
        return ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 3,
            'body' => 'The original review body text.',
            'photos' => $photos,
            'status' => 'approved',
        ]);
    }

    public function test_execute_throws_a_404_for_an_unknown_slug(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->action->execute('does-not-exist', User::factory()->create(), [
            'rating' => 5,
            'body' => 'An updated review body text.',
        ]);
    }

    public function test_execute_throws_when_the_user_has_no_review_for_the_product(): void
    {
        $product = $this->makeProduct();

        $this->expectException(ModelNotFoundException::class);

        $this->action->execute($product->slug, User::factory()->create(), [
            'rating' => 5,
            'body' => 'An updated review body text.',
        ]);
    }

    public function test_execute_updates_the_rating_title_and_body(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();
        $review = $this->makeReview($product, $user);

        $result = $this->action->execute($product->slug, $user, [
            'rating' => 5,
            'title' => 'Updated title',
            'body' => 'An updated review body text.',
        ]);

        $this->assertSame(5, $result['rating']);
        $this->assertSame('Updated title', $result['title']);
        $this->assertSame('An updated review body text.', $review->fresh()->body);
    }

    public function test_execute_only_keeps_photos_that_already_belong_to_the_review(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();
        $review = $this->makeReview($product, $user, ['https://cdn.example.com/real.jpg']);

        $result = $this->action->execute($product->slug, $user, [
            'rating' => 3,
            'body' => 'The original review body text.',
            'existing_photos' => [
                'https://cdn.example.com/real.jpg',
                'https://evil.example.com/injected.jpg',
            ],
        ]);

        $this->assertSame(['https://cdn.example.com/real.jpg'], $result['photos']);
    }

    public function test_execute_merges_kept_photos_with_newly_uploaded_ones(): void
    {
        Storage::fake('public');
        $product = $this->makeProduct();
        $user = User::factory()->create();
        $this->makeReview($product, $user, ['https://cdn.example.com/real.jpg']);

        $result = $this->action->execute($product->slug, $user, [
            'rating' => 3,
            'body' => 'The original review body text.',
            'existing_photos' => ['https://cdn.example.com/real.jpg'],
            'photos' => [UploadedFile::fake()->image('new.jpg')],
        ]);

        $this->assertCount(2, $result['photos']);
        $this->assertContains('https://cdn.example.com/real.jpg', $result['photos']);
    }
}
