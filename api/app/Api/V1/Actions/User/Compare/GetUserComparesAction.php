<?php

namespace App\Api\V1\Actions\User\Compare;

use App\Api\V1\Repositories\ProductRepository;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GetUserComparesAction
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {}

    public function execute(User $user): Collection
    {
        $productIds = $user->compares()->pluck('product_id')->toArray();

        return $this->productRepository->queryActive()->whereIn('id', $productIds)->get();
    }
}
