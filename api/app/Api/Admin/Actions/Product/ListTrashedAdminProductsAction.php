<?php

namespace App\Api\Admin\Actions\Product;

use App\Api\V1\Repositories\ProductRepository;
use Illuminate\Database\Eloquent\Collection;

class ListTrashedAdminProductsAction
{
    public function __construct(
        protected ProductRepository $productRepository
    ) {}

    public function execute(): Collection
    {
        return $this->productRepository->trashed();
    }
}
