<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\GetHomeDataAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class HomeController extends BaseApiController
{
    #[OA\Get(
        path: '/api/v1/catalog/home',
        summary: 'Get home page data: active banners, popular categories, flash deals and personalized recommendations',
        tags: [
            'Home',
        ],
        parameters: [
            new OA\Parameter(
                name: 'wishlist_ids',
                description: 'Comma-separated product ids from the visitor\'s wishlist, used to seed recommendations',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'viewed_ids',
                description: 'Comma-separated product ids the visitor recently viewed, used to seed recommendations',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Home page data',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'banners',
                                    description: 'Active home banners, ordered by sort order',
                                    type: 'array',
                                    items: new OA\Items(type: 'object'),
                                ),
                                new OA\Property(
                                    property: 'categories',
                                    description: 'The 6 categories with the most active products',
                                    type: 'array',
                                    items: new OA\Items(type: 'object'),
                                ),
                                new OA\Property(
                                    property: 'flashDeals',
                                    description: 'Up to 4 active products, rotated hourly',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/ProductSummary'),
                                ),
                                new OA\Property(
                                    property: 'recommended',
                                    description: 'Up to 8 products: wishlist/viewed-based matches, topped up with is_recommended products and random active products',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/ProductSummary'),
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function homeData(Request $request, GetHomeDataAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($request));
    }
}
