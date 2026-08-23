<?php

namespace App\Api\Admin\Actions\HomeBanner;

use App\Api\Admin\Dto\HomeBannerDto;
use App\Api\V1\Exceptions\HomeBannerNotFoundException;
use App\Api\V1\Repositories\HomeBannerRepositoryInterface;
use App\Models\HomeBanner;

class UpdateAdminHomeBannerAction
{
    public function __construct(
        protected HomeBannerRepositoryInterface $homeBannerRepository
    ) {}

    public function execute(int $id, HomeBannerDto $dto): HomeBanner
    {
        $banner = $this->homeBannerRepository->find($id);

        if (! $banner) {
            throw new HomeBannerNotFoundException;
        }

        return $this->homeBannerRepository->update($banner, $dto->toArray());
    }
}
