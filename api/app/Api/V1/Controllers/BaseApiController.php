<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasApiJsonResponse;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Filkx API",
 *     version="1.0.0",
 *     description="API documentation for Filkx Video Streaming Platform",
 *
 *     @OA\Contact(
 *         email="admin@filkx.com"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="Primary API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
abstract class BaseApiController extends Controller
{
    use HasApiJsonResponse;

    public const int PER_PAGE = 10;
}
