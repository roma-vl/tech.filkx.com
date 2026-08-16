<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Newsletter\SubscribeNewsletterAction;
use App\Api\V1\Requests\SubscribeNewsletterRequest;
use Illuminate\Http\JsonResponse;

class NewsletterController extends BaseApiController
{
    public function subscribe(SubscribeNewsletterRequest $request, SubscribeNewsletterAction $action): JsonResponse
    {
        $action->execute($request->validated()['email']);

        return self::successfulResponse();
    }
}
