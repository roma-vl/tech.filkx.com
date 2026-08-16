<?php

namespace App\Api\Admin\Actions\HomeBanner;

use App\Api\V1\Exceptions\HomeBannerNotFoundException;
use App\Api\V1\Repositories\HomeBannerRepositoryInterface;

class DeleteAdminHomeBannerAction
{
    public function __construct(
        protected HomeBannerRepositoryInterface $homeBannerRepository
    ) {}

    public function execute(int $id): void
    {
        $banner = $this->homeBannerRepository->find($id);

        if (! $banner) {
            throw new HomeBannerNotFoundException;
        }

        $this->homeBannerRepository->delete($banner);
    }
}
