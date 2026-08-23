<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\GetPromoPageAction;
use App\Api\V1\Resources\PromoPageResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PromoPageController extends BaseApiController
{
    #[OA\Get(
        path: '/api/v1/promo/{slug}',
        summary: 'Get an active promo page by slug, with its curated product list',
        tags: ['Promo'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Promo page with its products',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'object'),
                    ]
                )
            ),
            new OA\Response(response: 404, description: 'Promo page not found or inactive'),
        ]
    )]
    public function show(string $slug, GetPromoPageAction $action): JsonResponse
    {
        return self::successfulResponseWithData(new PromoPageResource($action->execute($slug)));
    }
}
