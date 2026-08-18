<?php

namespace App\Api\V1\Actions\Review;

use App\Api\V1\Repositories\ProductRepository;
use App\Models\ProductReview;
use App\Models\User;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CreateProductReviewAction
{
    public function __construct(
        protected ProductRepository $productRepository,
        protected UploadReviewPhotosAction $uploadReviewPhotosAction
    ) {}

    public function execute(string $slug, User $user, array $validated): array
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            abort(404, 'Product not found.');
        }

        $existing = ProductReview::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->first();

        if ($existing) {
            throw new UnprocessableEntityHttpException('Ви вже залишали відгук на цей товар.');
        }

        $photoUrls = $this->uploadReviewPhotosAction->execute($product->id, $validated['photos'] ?? []);

        $review = ProductReview::create([
            'product_id' => $product->id,
            'user_id' => $user->id,
            'order_id' => $validated['order_id'] ?? null,
            'rating' => (int) $validated['rating'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
            'photos' => $photoUrls ?: null,
            'status' => 'approved',
        ]);

        return [
            'id' => $review->id,
            'rating' => $review->rating,
            'title' => $review->title,
            'body' => $review->body,
            'photos' => $review->photos ?? [],
            'author' => $user->name ?? 'Анонім',
            'created_at' => $review->created_at->toISOString(),
        ];
    }
}
