<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\GetHomeDataAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends BaseApiController
{
    public function homeData(Request $request, GetHomeDataAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($request));
    }
}
