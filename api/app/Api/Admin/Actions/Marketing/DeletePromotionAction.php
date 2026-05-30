<?php

namespace App\Api\Admin\Actions\Marketing;

use App\Api\V1\Exceptions\PromotionNotFoundException;
use App\Api\V1\Repositories\PromotionRepositoryInterface;

class DeletePromotionAction
{
    public function __construct(
        protected PromotionRepositoryInterface $promotionRepository
    ) {}

    public function execute(int $id): void
    {
        $promotion = $this->promotionRepository->find($id);

        if (! $promotion) {
            throw new PromotionNotFoundException();
        }

        $this->promotionRepository->delete($promotion);
    }
}
