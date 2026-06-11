<?php

namespace App\Api\Admin\Actions\Marketing;

use App\Api\Admin\Dto\PromotionDto;
use App\Api\V1\Exceptions\PromotionNotFoundException;
use App\Api\V1\Repositories\PromotionRepositoryInterface;
use App\Models\Promotion;

class UpdatePromotionAction
{
    public function __construct(
        protected PromotionRepositoryInterface $promotionRepository
    ) {}

    public function execute(int $id, PromotionDto $dto): Promotion
    {
        $promotion = $this->promotionRepository->find($id);

        if (! $promotion) {
            throw new PromotionNotFoundException;
        }

        return $this->promotionRepository->update($promotion, $dto->toArray());
    }
}
