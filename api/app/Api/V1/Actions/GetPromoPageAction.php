<?php

namespace App\Api\V1\Actions;

use App\Api\V1\Exceptions\PromoPageNotFoundException;
use App\Api\V1\Repositories\PromoPageRepositoryInterface;
use App\Models\PromoPage;

class GetPromoPageAction
{
    public function __construct(
        protected PromoPageRepositoryInterface $promoPageRepository
    ) {}

    public function execute(string $slug): PromoPage
    {
        $promoPage = $this->promoPageRepository->findActiveBySlugWithProducts($slug);

        if (! $promoPage) {
            throw new PromoPageNotFoundException;
        }

        return $promoPage;
    }
}
