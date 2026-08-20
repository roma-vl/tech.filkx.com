<?php

namespace App\Api\V1\Actions;

use App\Api\V1\Repositories\BrandRepository;
use App\Api\V1\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;

class ListBrandsAction
{
    public function __construct(
        protected BrandRepository $brandRepository,
        protected CategoryRepository $categoryRepository
    ) {}

    public function execute(?string $categorySlug = null): Collection
    {
        $categoryIds = $categorySlug ? $this->categoryRepository->resolveCategoryIdsBySlug($categorySlug) : [];

        return $this->brandRepository->getBrandsWithActiveProductsCount($categoryIds);
    }
}
