<?php

namespace App\Api\V1\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasApiJsonResponse;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    description: 'API documentation for the Tech Filkx e-commerce platform',
    title: 'Filkx API',
    contact: new OA\Contact(email: 'admin@filkx.com'),
)]
#[OA\Server(
    url: L5_SWAGGER_CONST_HOST,
    description: 'Primary API Server',
)]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    bearerFormat: 'JWT',
    scheme: 'bearer',
)]
abstract class BaseApiController extends Controller
{
    use HasApiJsonResponse;

    public const int PER_PAGE = 10;
}
