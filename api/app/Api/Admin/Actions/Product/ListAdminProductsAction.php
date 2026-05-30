<?php

namespace App\Api\Admin\Actions\Product;

use App\Api\V1\Repositories\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ListAdminProductsAction
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function execute(): Collection
    {
        return $this->productRepository->all();
    }
}
