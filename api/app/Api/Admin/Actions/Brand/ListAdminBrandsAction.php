<?php

namespace App\Api\Admin\Actions\Brand;

use App\Api\V1\Repositories\BrandRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ListAdminBrandsAction
{
    public function __construct(
        protected BrandRepositoryInterface $brandRepository
    ) {}

    public function execute(): Collection
    {
        return $this->brandRepository->all();
    }
}
