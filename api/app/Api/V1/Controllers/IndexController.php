<?php

namespace App\Api\V1\Controllers;

use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;

class IndexController extends BaseApiController
{
    /**
     * @OA\Get(
     *     path="/api",
     *     summary="API Health Check / Validation",
     *     tags={"System"},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *
     *         @OA\JsonContent(
     *
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="api", type="string", example="Hello Api!")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        return self::successfulResponseWithData([
            'api' => 'Hello Api!!!',
        ]);
    }
}
