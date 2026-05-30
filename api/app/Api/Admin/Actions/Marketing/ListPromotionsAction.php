<?php

namespace App\Api\Admin\Actions\Marketing;

use App\Api\Admin\Dto\MarketingFilterDto;
use App\Api\V1\Repositories\PromotionRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPromotionsAction
{
    public function __construct(
        protected PromotionRepositoryInterface $promotionRepository
    ) {}

    public function execute(MarketingFilterDto $dto, int $perPage): LengthAwarePaginator
    {
        return $this->promotionRepository->paginate($dto->toArray(), $perPage);
    }
}
