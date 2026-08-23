<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Newsletter\SubscribeNewsletterAction;
use App\Api\V1\Requests\SubscribeNewsletterRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class NewsletterController extends BaseApiController
{
    #[OA\Post(
        path: '/api/v1/newsletter/subscribe',
        summary: 'Subscribe an email address to the newsletter (idempotent)',
        tags: [
            'Newsletter',
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email'],
                properties: [
                    new OA\Property(property: 'email', type: 'string', format: 'email'),
                ],
            ),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Subscribed successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                    ],
                ),
            ),
            new OA\Response(
                response: 422,
                description: 'Validation failed',
            ),
        ],
    )]
    public function subscribe(SubscribeNewsletterRequest $request, SubscribeNewsletterAction $action): JsonResponse
    {
        $action->execute($request->validated()['email']);

        return self::successfulResponse();
    }
}
