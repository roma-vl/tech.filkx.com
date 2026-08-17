<?php

namespace App\Api\V1\Actions;

use App\Api\V1\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;

class GetRelatedProductsAction
{
    public function __construct(
        protected ProductRepository $productRepository
    ) {}

    public function execute(string $slug): Collection
    {
        $product = $this->productRepository->findBySlug($slug);

        if (! $product) {
            abort(404, 'Product not found.');
        }

        return $this->productRepository->getRelated($product);
    }
}
