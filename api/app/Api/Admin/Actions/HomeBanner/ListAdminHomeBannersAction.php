<?php

namespace App\Api\Admin\Actions\HomeBanner;

use App\Api\V1\Repositories\HomeBannerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ListAdminHomeBannersAction
{
    public function __construct(
        protected HomeBannerRepositoryInterface $homeBannerRepository
    ) {}

    public function execute(): Collection
    {
        return $this->homeBannerRepository->all();
    }
}
