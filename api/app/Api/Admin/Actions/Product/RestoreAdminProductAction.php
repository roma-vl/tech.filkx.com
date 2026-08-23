<?php

namespace App\Api\Admin\Actions\Product;

use App\Api\V1\Exceptions\ProductNotFoundException;
use App\Api\V1\Exceptions\ProductSlugConflictException;
use App\Api\V1\Repositories\ProductRepository;
use App\Models\Product;

class RestoreAdminProductAction
{
    public function __construct(
        protected ProductRepository $productRepository
    ) {}

    public function execute(int $id): Product
    {
        $product = $this->productRepository->findTrashed($id);

        if (! $product) {
            throw new ProductNotFoundException;
        }

        // The slug was freed for reuse while the product was in the trash -
        // if another product has since claimed it, restoring would violate
        // the partial unique index on products.slug.
        if ($this->productRepository->slugExists($product->slug)) {
            throw new ProductSlugConflictException(
                "Cannot restore product: slug \"{$product->slug}\" is already used by another product."
            );
        }

        $this->productRepository->restore($product);

        return $product->fresh(['brand', 'categories', 'variants.stocks']);
    }
}
