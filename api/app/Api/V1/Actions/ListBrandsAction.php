<?php

namespace App\Api\V1\Actions;

use App\Api\V1\Repositories\BrandRepository;
use Illuminate\Database\Eloquent\Collection;

class ListBrandsAction
{
    public function __construct(
        protected BrandRepository $brandRepository
    ) {}

    public function execute(): Collection
    {
        return $this->brandRepository->getBrandsWithActiveProductsCount();
    }
}
