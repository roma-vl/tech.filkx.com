<?php

namespace Tests\Unit\Actions\Review;

use App\Api\V1\Actions\Review\CreateProductReviewAction;
use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Tests\TestCase;

class CreateProductReviewActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateProductReviewAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateProductReviewAction::class);
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

    public function test_execute_throws_a_404_for_an_unknown_slug(): void
    {
        $this->expectException(NotFoundHttpException::class);

        $this->action->execute('does-not-exist', User::factory()->create(), [
            'rating' => 5,
            'body' => 'A perfectly average review body.',
        ]);
    }

    public function test_execute_creates_an_approved_review(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create(['name' => 'Олена']);

        $result = $this->action->execute($product->slug, $user, [
            'rating' => 4,
            'title' => 'Nice',
            'body' => 'A perfectly average review body.',
            'order_id' => null,
        ]);

        $this->assertSame(4, $result['rating']);
        $this->assertSame('Nice', $result['title']);
        $this->assertSame('Олена', $result['author']);
        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 4,
            'status' => 'approved',
        ]);
    }

    public function test_execute_rejects_a_second_review_from_the_same_user(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();

        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
            'body' => 'First review body text.',
            'status' => 'approved',
        ]);

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->expectExceptionMessage('Ви вже залишали відгук на цей товар.');

        $this->action->execute($product->slug, $user, [
            'rating' => 3,
            'body' => 'Second review body text.',
        ]);
    }

    public function test_execute_uploads_and_stores_photo_urls(): void
    {
        Storage::fake('public');
        $product = $this->makeProduct();
        $user = User::factory()->create();

        $result = $this->action->execute($product->slug, $user, [
            'rating' => 5,
            'body' => 'A perfectly average review body.',
            'photos' => [UploadedFile::fake()->image('photo.jpg')],
        ]);

        $this->assertCount(1, $result['photos']);
        $review = ProductReview::first();
        $this->assertNotNull($review->photos);
        $this->assertCount(1, Storage::disk('public')->files("reviews/{$product->id}"));
    }
}
