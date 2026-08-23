<?php

namespace App\Api\Admin\Actions\PromoPage;

use App\Api\V1\Exceptions\PromoPageNotFoundException;
use App\Api\V1\Repositories\PromoPageRepositoryInterface;

class DeleteAdminPromoPageAction
{
    public function __construct(
        protected PromoPageRepositoryInterface $promoPageRepository
    ) {}

    public function execute(int $id): void
    {
        $promoPage = $this->promoPageRepository->find($id);

        if (! $promoPage) {
            throw new PromoPageNotFoundException;
        }

        $this->promoPageRepository->delete($promoPage);
    }
}
