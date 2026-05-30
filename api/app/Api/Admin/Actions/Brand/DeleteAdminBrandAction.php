<?php

namespace App\Api\Admin\Actions\Brand;

use App\Api\V1\Exceptions\BrandNotFoundException;
use App\Api\V1\Repositories\BrandRepositoryInterface;

class DeleteAdminBrandAction
{
    public function __construct(
        protected BrandRepositoryInterface $brandRepository
    ) {}

    public function execute(int $id): void
    {
        $brand = $this->brandRepository->find($id);

        if (! $brand) {
            throw new BrandNotFoundException();
        }

        $this->brandRepository->delete($brand);
    }
}
