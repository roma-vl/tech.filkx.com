<?php

namespace App\Api\V1\Actions\User\Compare;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class SyncUserComparesAction
{
    public function __construct(
        private readonly GetUserComparesAction $getUserComparesAction
    ) {}

    public function execute(User $user, array $productIds): Collection
    {
        if (! empty($productIds)) {
            $user->compares()->syncWithoutDetaching($productIds);
        }

        return $this->getUserComparesAction->execute($user);
    }
}
