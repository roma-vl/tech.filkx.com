<?php

namespace App\Api\V1\Actions;

use App\Api\V1\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;

class ListCategoriesAction
{
    public function __construct(
        protected CategoryRepository $categoryRepository
    ) {}

    public function execute(): Collection
    {
        return $this->categoryRepository->getParentCategoriesWithChildren();
    }
}
