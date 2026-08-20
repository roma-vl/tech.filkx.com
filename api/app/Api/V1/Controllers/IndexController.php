<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class IndexController extends BaseApiController
{
    #[OA\Get(
        path: '/api/index',
        summary: 'API health check / validation',
        tags: [
            'System',
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'status',
                            type: 'string',
                            example: 'success',
                        ),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'api',
                                    type: 'string',
                                    example: 'Hello Api!!!',
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(): JsonResponse
    {
        return self::successfulResponseWithData([
            'api' => 'Hello Api!!!',
        ]);
    }
}
