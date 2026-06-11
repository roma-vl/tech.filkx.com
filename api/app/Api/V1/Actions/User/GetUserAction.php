<?php

namespace App\Api\V1\Actions\User;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class GetUserAction
{
    public function execute(): User
    {
        return Auth::guard('api')->user();
    }
}
