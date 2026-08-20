<?php

namespace Tests\Feature\Review;

use App\Models\Product;
use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReviewControllerTest extends TestCase
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

    private function authHeader(User $user): array
    {
        $token = $user->createToken('api-access')->accessToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_index_returns_only_approved_reviews_with_stats(): void
    {
        $product = $this->makeProduct();
        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => User::factory()->create()->id,
            'rating' => 5,
            'body' => 'A perfectly average review body.',
            'status' => 'approved',
        ]);
        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => User::factory()->create()->id,
            'rating' => 1,
            'body' => 'A pending review body text.',
            'status' => 'pending',
        ]);

        $response = $this->getJson("/api/v1/catalog/products/{$product->slug}/reviews");

        $response->assertOk()
            ->assertJsonCount(1, 'data.reviews')
            ->assertJsonPath('data.stats.count', 1);
    }

    public function test_index_returns_404_for_an_unknown_product(): void
    {
        $response = $this->getJson('/api/v1/catalog/products/does-not-exist/reviews');

        $response->assertStatus(404);
    }

    public function test_store_requires_authentication(): void
    {
        $product = $this->makeProduct();

        $response = $this->postJson("/api/v1/catalog/products/{$product->slug}/reviews", [
            'rating' => 5,
            'body' => 'A perfectly average review body.',
        ]);

        $response->assertStatus(401);
    }

    public function test_store_creates_a_review(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson("/api/v1/catalog/products/{$product->slug}/reviews", [
                'rating' => 5,
                'title' => 'Great',
                'body' => 'A perfectly average review body.',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.rating', 5)
            ->assertJsonPath('data.title', 'Great');
        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
        ]);
    }

    public function test_store_validates_the_rating_range(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson("/api/v1/catalog/products/{$product->slug}/reviews", [
                'rating' => 6,
                'body' => 'A perfectly average review body.',
            ]);

        $response->assertStatus(422)->assertJsonPath('status', 'error');
    }

    public function test_store_rejects_a_second_review_from_the_same_user(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();
        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 4,
            'body' => 'The first review body text.',
            'status' => 'approved',
        ]);

        $response = $this->withHeaders($this->authHeader($user))
            ->postJson("/api/v1/catalog/products/{$product->slug}/reviews", [
                'rating' => 5,
                'body' => 'The second review body text.',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Ви вже залишали відгук на цей товар.');
    }

    public function test_store_uploads_photos(): void
    {
        Storage::fake('public');
        $product = $this->makeProduct();
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))
            ->post("/api/v1/catalog/products/{$product->slug}/reviews", [
                'rating' => 5,
                'body' => 'A perfectly average review body.',
                'photos' => [UploadedFile::fake()->image('photo.jpg')],
            ]);

        $response->assertStatus(201)
            ->assertJsonCount(1, 'data.photos');
    }

    public function test_update_requires_authentication(): void
    {
        $product = $this->makeProduct();

        $response = $this->putJson("/api/v1/catalog/products/{$product->slug}/reviews", [
            'rating' => 5,
            'body' => 'An updated review body text.',
        ]);

        $response->assertStatus(401);
    }

    public function test_update_returns_404_when_the_user_has_no_review(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();

        $response = $this->withHeaders($this->authHeader($user))
            ->putJson("/api/v1/catalog/products/{$product->slug}/reviews", [
                'rating' => 5,
                'body' => 'An updated review body text.',
            ]);

        $response->assertStatus(404);
    }

    public function test_update_updates_the_users_review(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();
        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 3,
            'body' => 'The original review body text.',
            'status' => 'approved',
        ]);

        $response = $this->withHeaders($this->authHeader($user))
            ->putJson("/api/v1/catalog/products/{$product->slug}/reviews", [
                'rating' => 5,
                'body' => 'An updated review body text.',
            ]);

        $response->assertOk()->assertJsonPath('data.rating', 5);
        $this->assertDatabaseHas('product_reviews', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 5,
        ]);
    }

    public function test_my_reviews_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/user/reviews');

        $response->assertStatus(401);
    }

    public function test_my_reviews_returns_the_authenticated_users_reviews(): void
    {
        $product = $this->makeProduct();
        $user = User::factory()->create();
        ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'rating' => 4,
            'body' => 'My own review body text.',
            'status' => 'approved',
        ]);

        $response = $this->withHeaders($this->authHeader($user))
            ->getJson('/api/v1/user/reviews');

        $response->assertOk()->assertJsonCount(1, 'data');
    }
}
