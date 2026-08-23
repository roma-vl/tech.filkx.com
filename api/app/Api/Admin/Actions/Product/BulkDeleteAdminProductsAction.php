<?php

namespace App\Api\Admin\Actions\Product;

use App\Api\V1\Repositories\ProductRepositoryInterface;
use App\Models\Product;

class BulkDeleteAdminProductsAction
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    /**
     * @param  array<int>  $ids
     * @return int Number of products actually deleted (ids that no longer
     *              existed are silently skipped rather than failing the batch).
     */
    public function execute(array $ids): int
    {
        $count = 0;

        foreach (Product::whereIn('id', $ids)->get() as $product) {
            // One at a time via the repository (not a single whereIn()->delete())
            // so Eloquent's deleting/deleted events fire per model - that's what
            // keeps Scout's search index in sync, and what the repository relies
            // on to also soft-delete the product's own variants.
            $this->productRepository->delete($product);
            $count++;
        }

        return $count;
    }
}
