<?php

namespace App\Api\Admin\Actions\HomeBanner;

use App\Api\Admin\Dto\HomeBannerDto;
use App\Api\V1\Repositories\HomeBannerRepositoryInterface;
use App\Models\HomeBanner;

class CreateAdminHomeBannerAction
{
    public function __construct(
        protected HomeBannerRepositoryInterface $homeBannerRepository
    ) {}

    public function execute(HomeBannerDto $dto): HomeBanner
    {
        return $this->homeBannerRepository->create($dto->toArray());
    }
}
