<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Category\CreateCategoryAction;
use App\Api\Admin\Actions\Category\DeleteCategoryAction;
use App\Api\Admin\Actions\Category\ListCategoriesAction;
use App\Api\Admin\Actions\Category\UpdateCategoryAction;
use App\Api\Admin\Dto\CategoryDto;
use App\Api\Admin\Requests\StoreCategoryRequest;
use App\Api\Admin\Requests\UpdateCategoryRequest;
use App\Api\Admin\Resources\AdminCategoryResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class AdminCategoryController extends BaseApiController
{
    #[OA\Get(
        path: '/api/admin/categories',
        summary: 'List catalog categories',
        security: [['bearerAuth' => []]],
        tags: ['Admin Categories'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/AdminCategory'),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(ListCategoriesAction $action): JsonResponse
    {
        $categories = $action->execute();

        return self::successfulResponseWithData(AdminCategoryResource::collection($categories));
    }

    #[OA\Post(
        path: '/api/admin/categories',
        summary: 'Create a catalog category',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nameUk', 'nameEn'],
                properties: [
                    new OA\Property(property: 'nameUk', type: 'string'),
                    new OA\Property(property: 'nameEn', type: 'string'),
                    new OA\Property(property: 'parentId', type: 'integer', nullable: true),
                    new OA\Property(property: 'order', type: 'integer'),
                ],
            ),
        ),
        tags: ['Admin Categories'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Category created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AdminCategory'),
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(StoreCategoryRequest $request, CreateCategoryAction $action): JsonResponse
    {
        $category = $action->execute(CategoryDto::fromRequest($request));

        return self::successfulResponseWithData(new AdminCategoryResource($category), Response::HTTP_CREATED);
    }

    #[OA\Put(
        path: '/api/admin/categories/{id}',
        summary: 'Update a catalog category',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nameUk', 'nameEn'],
                properties: [
                    new OA\Property(property: 'nameUk', type: 'string'),
                    new OA\Property(property: 'nameEn', type: 'string'),
                    new OA\Property(property: 'parentId', type: 'integer', nullable: true),
                    new OA\Property(property: 'order', type: 'integer'),
                ],
            ),
        ),
        tags: ['Admin Categories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AdminCategory'),
            ),
            new OA\Response(response: 404, description: 'Category not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function update(UpdateCategoryRequest $request, int $id, UpdateCategoryAction $action): JsonResponse
    {
        $category = $action->execute($id, CategoryDto::fromRequest($request));

        return self::successfulResponseWithData(new AdminCategoryResource($category));
    }

    #[OA\Delete(
        path: '/api/admin/categories/{id}',
        summary: 'Delete a catalog category',
        security: [['bearerAuth' => []]],
        tags: ['Admin Categories'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Category deleted successfully'),
            new OA\Response(response: 404, description: 'Category not found'),
        ],
    )]
    public function destroy(int $id, DeleteCategoryAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse();
    }
}
