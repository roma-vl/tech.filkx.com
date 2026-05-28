<?php

namespace App\Api\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Traits\HasApiJsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class BaseApiController extends Controller
{
    use AuthorizesRequests, HasApiJsonResponse;

    public const PER_PAGE = 10;
}
