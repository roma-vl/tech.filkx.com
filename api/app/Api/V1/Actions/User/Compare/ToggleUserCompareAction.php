<?php

namespace App\Api\V1\Actions\User\Compare;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class ToggleUserCompareAction
{
    public function __construct(
        private readonly GetUserComparesAction $getUserComparesAction
    ) {}

    public function execute(User $user, int $productId): Collection
    {
        $user->compares()->toggle($productId);

        return $this->getUserComparesAction->execute($user);
    }
}
