<?php

namespace App\Api\V1\Actions\User\ViewedProducts;

use App\Api\V1\Repositories\ProductRepository;
use App\Models\User;
use Illuminate\Support\Collection;

class GetUserViewedProductsAction
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {}

    public function execute(User $user): Collection
    {
        $viewedItems = $user->viewedProducts()
            ->withPivot('view_count')
            ->orderByPivot('updated_at', 'desc')
            ->get();

        $productIds = $viewedItems->pluck('id')->toArray();
        $products = $this->productRepository->queryActive()->whereIn('id', $productIds)->get();

        $productsMapped = $products->map(function ($product) use ($viewedItems) {
            $viewed = $viewedItems->firstWhere('id', $product->id);
            $product->view_count = $viewed ? $viewed->pivot->view_count : 1;
            $product->last_viewed_at = $viewed ? $viewed->pivot->updated_at->toISOString() : now()->toISOString();

            return $product;
        });

        return $productsMapped->sortByDesc('last_viewed_at')->values();
    }
}
