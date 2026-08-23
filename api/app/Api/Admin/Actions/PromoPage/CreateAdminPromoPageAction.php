<?php

namespace App\Api\Admin\Actions\PromoPage;

use App\Api\Admin\Dto\PromoPageDto;
use App\Api\V1\Repositories\PromoPageRepositoryInterface;
use App\Models\PromoPage;

class CreateAdminPromoPageAction
{
    public function __construct(
        protected PromoPageRepositoryInterface $promoPageRepository,
        protected GenerateUniquePromoPageSlugAction $generateUniquePromoPageSlugAction
    ) {}

    public function execute(PromoPageDto $dto): PromoPage
    {
        $data = $dto->toArray();
        $data['slug'] = $this->generateUniquePromoPageSlugAction->execute($dto->title);

        $promoPage = $this->promoPageRepository->create($data);
        $this->promoPageRepository->syncProducts($promoPage, $dto->productIds);

        return $promoPage->load('products');
    }
}
