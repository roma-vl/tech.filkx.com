<?php

namespace App\Api\Admin\Actions\PromoPage;

use App\Api\V1\Repositories\PromoPageRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ListAdminPromoPagesAction
{
    public function __construct(
        protected PromoPageRepositoryInterface $promoPageRepository
    ) {}

    public function execute(): Collection
    {
        return $this->promoPageRepository->all();
    }
}
