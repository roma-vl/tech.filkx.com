<?php

namespace App\Api\Admin\Actions\Brand;

use App\Api\Admin\Dto\BrandDto;
use App\Api\V1\Repositories\BrandRepositoryInterface;
use App\Models\Brand;

class CreateAdminBrandAction
{
    public function __construct(
        protected BrandRepositoryInterface $brandRepository
    ) {}

    public function execute(BrandDto $dto): Brand
    {
        return $this->brandRepository->create($dto->toArray());
    }
}
