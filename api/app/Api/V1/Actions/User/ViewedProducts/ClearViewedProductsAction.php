<?php

namespace App\Api\V1\Actions\User\ViewedProducts;

use App\Models\User;

class ClearViewedProductsAction
{
    public function execute(User $user): void
    {
        $user->viewedProducts()->detach();
    }
}
