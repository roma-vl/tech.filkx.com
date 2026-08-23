<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Page\CreatePageAction;
use App\Api\Admin\Actions\Page\DeletePageAction;
use App\Api\Admin\Actions\Page\GetPageAction;
use App\Api\Admin\Actions\Page\ListPagesAction;
use App\Api\Admin\Actions\Page\UpdatePageAction;
use App\Api\Admin\Requests\StorePageRequest;
use App\Api\Admin\Requests\UpdatePageRequest;
use App\Api\Admin\Resources\PageResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class AdminPageController extends BaseApiController
{
    #[OA\Get(
        path: '/api/admin/pages',
        summary: 'List static pages',
        security: [['bearerAuth' => []]],
        tags: ['Admin Pages'],
        parameters: [
            new OA\Parameter(
                name: 'search',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'per_page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of static pages',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/AdminPage'),
                                ),
                                new OA\Property(
                                    property: 'meta',
                                    properties: [
                                        new OA\Property(property: 'total', type: 'integer'),
                                        new OA\Property(property: 'per_page', type: 'integer'),
                                        new OA\Property(property: 'current_page', type: 'integer'),
                                        new OA\Property(property: 'last_page', type: 'integer'),
                                    ],
                                    type: 'object',
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function index(Request $request, ListPagesAction $action): JsonResponse
    {
        $paginated = $action->execute($request->string('search')->value() ?: null, (int) $request->input('per_page', 20));

        return self::successfulResponseWithData([
            'data' => PageResource::collection($paginated),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/admin/pages/{id}',
        summary: 'Get a static page',
        security: [['bearerAuth' => []]],
        tags: ['Admin Pages'],
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
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/AdminPage'),
            ),
            new OA\Response(response: 404, description: 'Page not found'),
        ],
    )]
    public function show(int $id, GetPageAction $action): JsonResponse
    {
        $page = $action->execute($id);

        return self::successfulResponseWithData(new PageResource($page, withContent: true));
    }

    #[OA\Post(
        path: '/api/admin/pages',
        summary: 'Create a static page',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['titleUk', 'titleEn', 'contentUk', 'contentEn', 'status'],
                properties: [
                    new OA\Property(property: 'titleUk', type: 'string', example: 'Про нас'),
                    new OA\Property(property: 'titleEn', type: 'string', example: 'About us'),
                    new OA\Property(property: 'contentUk', type: 'string'),
                    new OA\Property(property: 'contentEn', type: 'string'),
                    new OA\Property(property: 'slug', type: 'string', example: 'about-us'),
                    new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published']),
                ],
            ),
        ),
        tags: ['Admin Pages'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Page created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AdminPage'),
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function store(StorePageRequest $request, CreatePageAction $action): JsonResponse
    {
        $page = $action->execute($request->validated());

        return self::successfulResponseWithData(new PageResource($page, withContent: true), Response::HTTP_CREATED);
    }

    #[OA\Put(
        path: '/api/admin/pages/{id}',
        summary: 'Update a static page',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['titleUk', 'titleEn', 'contentUk', 'contentEn', 'slug', 'status'],
                properties: [
                    new OA\Property(property: 'titleUk', type: 'string'),
                    new OA\Property(property: 'titleEn', type: 'string'),
                    new OA\Property(property: 'contentUk', type: 'string'),
                    new OA\Property(property: 'contentEn', type: 'string'),
                    new OA\Property(property: 'slug', type: 'string'),
                    new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published']),
                ],
            ),
        ),
        tags: ['Admin Pages'],
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
                description: 'Page updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AdminPage'),
            ),
            new OA\Response(response: 404, description: 'Page not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function update(UpdatePageRequest $request, int $id, UpdatePageAction $action): JsonResponse
    {
        $page = $action->execute($id, $request->validated());

        return self::successfulResponseWithData(new PageResource($page, withContent: true));
    }

    #[OA\Delete(
        path: '/api/admin/pages/{id}',
        summary: 'Delete a static page',
        security: [['bearerAuth' => []]],
        tags: ['Admin Pages'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'integer'),
            ),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Page deleted successfully'),
            new OA\Response(response: 404, description: 'Page not found'),
        ],
    )]
    public function destroy(int $id, DeletePageAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse();
    }
}
