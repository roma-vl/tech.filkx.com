<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\AuditLog\ListAuditLogsAction;
use App\Api\Admin\Resources\AuditLogResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAuditLogController extends BaseApiController
{
    public function index(Request $request, ListAuditLogsAction $action): JsonResponse
    {
        $paginated = $action->execute(
            [
                'domain' => $request->string('domain')->value() ?: null,
                'action' => $request->string('action')->value() ?: null,
                'userId' => $request->integer('user_id') ?: null,
                'search' => $request->string('search')->value() ?: null,
            ],
            (int) $request->input('perPage', 20)
        );

        return self::successfulResponseWithData(AuditLogResource::collection($paginated));
    }
}
