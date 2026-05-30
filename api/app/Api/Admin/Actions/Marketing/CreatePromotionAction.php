<?php

namespace App\Api\Admin\Actions\Marketing;

use App\Api\Admin\Dto\PromotionDto;
use App\Api\V1\Repositories\PromotionRepositoryInterface;
use App\Models\Promotion;

class CreatePromotionAction
{
    public function __construct(
        protected PromotionRepositoryInterface $promotionRepository
    ) {}

    public function execute(PromotionDto $dto): Promotion
    {
        return $this->promotionRepository->create($dto->toArray());
    }
}
