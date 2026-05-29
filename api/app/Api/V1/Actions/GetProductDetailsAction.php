<?php

namespace App\Api\V1\Actions;

use App\Api\V1\Repositories\ProductRepository;
use App\Models\Product;

class GetProductDetailsAction
{
    public function __construct(
        protected ProductRepository $productRepository
    ) {}

    public function execute(string $slug): Product
    {
        $product = $this->productRepository->findBySlug($slug);
        
        if (!$product) {
            abort(404, 'Product not found.');
        }

        $product->increment('views_count');

        return $product;
    }
}
