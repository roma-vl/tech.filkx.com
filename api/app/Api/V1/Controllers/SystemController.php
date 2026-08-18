<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class SystemController extends BaseApiController
{
    #[OA\Get(
        path: '/api/system/status',
        summary: 'Get public system status',
        tags: [
            'System',
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'System status',
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
                                    property: 'maintenanceMode',
                                    type: 'boolean',
                                    example: false,
                                ),
                                new OA\Property(
                                    property: 'version',
                                    type: 'string',
                                    example: '1.0.0',
                                ),
                                new OA\Property(
                                    property: 'timestamp',
                                    type: 'string',
                                    format: 'date-time',
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function status(): JsonResponse
    {
        return self::successfulResponseWithData([
            'maintenance_mode' => app()->isDownForMaintenance(),
            'version' => config('app.version', '1.0.0'),
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
