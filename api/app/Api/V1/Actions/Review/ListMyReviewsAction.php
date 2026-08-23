<?php

namespace App\Api\V1\Actions\Review;

use App\Models\ProductReview;
use App\Models\User;
use Illuminate\Support\Collection;

class ListMyReviewsAction
{
    public function execute(User $user): Collection
    {
        return ProductReview::with('product:id,slug')
            ->where('user_id', $user->id)
            ->get()
            ->map(fn (ProductReview $review) => [
                'id' => $review->id,
                'product_slug' => $review->product?->slug,
                'product_id' => $review->product_id,
                'rating' => $review->rating,
                'title' => $review->title,
                'body' => $review->body,
                'photos' => $review->photos ?? [],
                'created_at' => $review->created_at->toISOString(),
            ]);
    }
}
