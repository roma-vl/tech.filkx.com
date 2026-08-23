<?php

namespace App\Api\Admin\Actions\PromoPage;

use App\Api\Admin\Dto\PromoPageDto;
use App\Api\V1\Exceptions\PromoPageNotFoundException;
use App\Api\V1\Repositories\PromoPageRepositoryInterface;
use App\Models\PromoPage;

class UpdateAdminPromoPageAction
{
    public function __construct(
        protected PromoPageRepositoryInterface $promoPageRepository
    ) {}

    public function execute(int $id, PromoPageDto $dto): PromoPage
    {
        $promoPage = $this->promoPageRepository->find($id);

        if (! $promoPage) {
            throw new PromoPageNotFoundException;
        }

        // The slug is set once at creation and never changes on update, so
        // previously shared/bookmarked promo links keep working.
        $promoPage = $this->promoPageRepository->update($promoPage, $dto->toArray());
        $this->promoPageRepository->syncProducts($promoPage, $dto->productIds);

        return $promoPage->load('products');
    }
}
