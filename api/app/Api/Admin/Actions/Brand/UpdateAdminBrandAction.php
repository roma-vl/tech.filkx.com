<?php

namespace App\Api\Admin\Actions\Brand;

use App\Api\Admin\Dto\BrandDto;
use App\Api\V1\Exceptions\BrandNotFoundException;
use App\Api\V1\Repositories\BrandRepositoryInterface;
use App\Models\Brand;

class UpdateAdminBrandAction
{
    public function __construct(
        protected BrandRepositoryInterface $brandRepository
    ) {}

    public function execute(int $id, BrandDto $dto): Brand
    {
        $brand = $this->brandRepository->find($id);

        if (! $brand) {
            throw new BrandNotFoundException;
        }

        return $this->brandRepository->update($brand, $dto->toArray());
    }
}
