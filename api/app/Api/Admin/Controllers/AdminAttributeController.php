<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Attribute\CreateAdminAttributeAction;
use App\Api\Admin\Actions\Attribute\DeleteAdminAttributeAction;
use App\Api\Admin\Actions\Attribute\ListAdminAttributesAction;
use App\Api\Admin\Actions\Attribute\UpdateAdminAttributeAction;
use App\Api\Admin\Dto\AttributeDto;
use App\Api\Admin\Requests\StoreAttributeRequest;
use App\Api\Admin\Requests\UpdateAttributeRequest;
use App\Api\Admin\Resources\AttributeResource;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AdminAttributeController extends BaseApiController
{
    public function index(ListAdminAttributesAction $action): JsonResponse
    {
        $attributes = $action->execute();

        return self::successfulResponseWithData(AttributeResource::collection($attributes));
    }

    public function store(StoreAttributeRequest $request, CreateAdminAttributeAction $action): JsonResponse
    {
        $attribute = $action->execute(AttributeDto::fromRequest($request));

        return self::successfulResponseWithData(new AttributeResource($attribute), Response::HTTP_CREATED);
    }

    public function update(UpdateAttributeRequest $request, int $id, UpdateAdminAttributeAction $action): JsonResponse
    {
        $attribute = $action->execute($id, AttributeDto::fromRequest($request));

        return self::successfulResponseWithData(new AttributeResource($attribute));
    }

    public function destroy(int $id, DeleteAdminAttributeAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse();
    }
}
