<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Order\DeleteAdminOrderAction;
use App\Api\Admin\Actions\Order\GetAdminOrderDetailsAction;
use App\Api\Admin\Actions\Order\ListAdminOrdersAction;
use App\Api\Admin\Actions\Order\UpdateAdminOrderStatusAction;
use App\Api\Admin\Dto\UpdateOrderStatusDto;
use App\Api\Admin\Requests\UpdateOrderStatusRequest;
use App\Api\Admin\Resources\AdminOrderResource;
use Illuminate\Http\JsonResponse;

class AdminOrderController extends BaseApiController
{
    public function index(ListAdminOrdersAction $action): JsonResponse
    {
        $orders = $action->execute();

        return self::successfulResponseWithData(AdminOrderResource::collection($orders));
    }

    public function show(int $id, GetAdminOrderDetailsAction $action): JsonResponse
    {
        $order = $action->execute($id);

        return self::successfulResponseWithData($order);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, int $id, UpdateAdminOrderStatusAction $action): JsonResponse
    {
        $order = $action->execute($id, UpdateOrderStatusDto::fromRequest($request));

        return self::successfulResponseWithData($order);
    }

    public function destroy(int $id, DeleteAdminOrderAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse();
    }
}
