<?php

namespace App\Api\V1\Actions\Review;

use App\Api\V1\Repositories\ProductRepository;
use App\Models\ProductReview;
use App\Models\User;

class UpdateProductReviewAction
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

        $review = ProductReview::where('product_id', $product->id)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Only photos already on the review may be kept — the client-supplied
        // `existing_photos` list must not be used to attach arbitrary URLs.
        $keptPhotos = array_values(array_intersect(
            $validated['existing_photos'] ?? [],
            $review->photos ?? []
        ));
        $newPhotoUrls = $this->uploadReviewPhotosAction->execute($product->id, $validated['photos'] ?? []);
        $allPhotos = array_merge($keptPhotos, $newPhotoUrls);

        $review->update([
            'rating' => (int) $validated['rating'],
            'title' => $validated['title'] ?? null,
            'body' => $validated['body'],
            'photos' => $allPhotos ?: null,
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
