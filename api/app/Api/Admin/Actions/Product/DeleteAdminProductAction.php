<?php

namespace App\Api\Admin\Actions\Product;

use App\Api\V1\Exceptions\ProductNotFoundException;
use App\Api\V1\Repositories\ProductRepositoryInterface;

class DeleteAdminProductAction
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function execute(int $id): void
    {
        $product = $this->productRepository->find($id);

        if (! $product) {
            throw new ProductNotFoundException;
        }

        $this->productRepository->delete($product);
    }
}
