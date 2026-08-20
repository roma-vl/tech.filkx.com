<?php

namespace App\Api\V1\Actions\Review;

use App\Api\V1\Repositories\ProductRepository;
use App\Models\ProductReview;

class ListProductReviewsAction
{
    public function __construct(
        protected ProductRepository $productRepository
    ) {}

    public function execute(string $slug): array
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            abort(404, 'Product not found.');
        }

        $reviews = ProductReview::with('user:id,name')
            ->where('product_id', $product->id)
            ->where('status', 'approved')
            ->latest()
            ->get()
            ->map(fn (ProductReview $review) => $this->formatReview($review));

        $stats = [
            'count' => $reviews->count(),
            'avg' => $reviews->count() ? round($reviews->avg('rating'), 1) : 0,
            'distribution' => array_map(
                fn ($star) => $reviews->where('rating', $star)->count(),
                [5, 4, 3, 2, 1]
            ),
        ];

        return [
            'reviews' => $reviews,
            'stats' => $stats,
        ];
    }

    private function formatReview(ProductReview $review): array
    {
        return [
            'id' => $review->id,
            'rating' => $review->rating,
            'title' => $review->title,
            'body' => $review->body,
            'photos' => $review->photos ?? [],
            'author' => $review->user?->name ?? 'Анонім',
            'created_at' => $review->created_at->toISOString(),
        ];
    }
}
