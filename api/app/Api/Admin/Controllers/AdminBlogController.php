<?php

namespace App\Api\Admin\Controllers;

use App\Api\Admin\Actions\Blog\CreateBlogCategoryAction;
use App\Api\Admin\Actions\Blog\CreateBlogPostAction;
use App\Api\Admin\Actions\Blog\CreateBlogTagAction;
use App\Api\Admin\Actions\Blog\DeleteBlogCategoryAction;
use App\Api\Admin\Actions\Blog\DeleteBlogPostAction;
use App\Api\Admin\Actions\Blog\DeleteBlogTagAction;
use App\Api\Admin\Actions\Blog\GetAdminBlogPostAction;
use App\Api\Admin\Actions\Blog\ListAdminBlogCategoriesAction;
use App\Api\Admin\Actions\Blog\ListAdminBlogPostsAction;
use App\Api\Admin\Actions\Blog\ListAdminBlogTagsAction;
use App\Api\Admin\Actions\Blog\UpdateBlogCategoryAction;
use App\Api\Admin\Actions\Blog\UpdateBlogPostAction;
use App\Api\Admin\Actions\Blog\UpdateBlogTagAction;
use App\Api\Admin\Actions\Blog\UploadBlogImageAction;
use App\Api\Admin\Dto\BlogCategoryDto;
use App\Api\Admin\Dto\BlogPostDto;
use App\Api\Admin\Dto\BlogTagDto;
use App\Api\Admin\Requests\Blog\StoreBlogCategoryRequest;
use App\Api\Admin\Requests\Blog\StoreBlogPostRequest;
use App\Api\Admin\Requests\Blog\StoreBlogTagRequest;
use App\Api\Admin\Requests\Blog\UpdateBlogCategoryRequest;
use App\Api\Admin\Requests\Blog\UpdateBlogPostRequest;
use App\Api\Admin\Requests\Blog\UpdateBlogTagRequest;
use App\Api\Admin\Requests\Blog\UploadBlogImageRequest;
use App\Api\Admin\Resources\AdminBlogCategoryResource;
use App\Api\Admin\Resources\AdminBlogPostResource;
use App\Api\Admin\Resources\AdminBlogTagResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpFoundation\Response;

class AdminBlogController extends BaseApiController
{
    #[OA\Get(
        path: '/api/admin/blog/posts',
        summary: 'List blog posts',
        security: [['bearerAuth' => []]],
        tags: ['Admin Blog'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', required: false, schema: new OA\Schema(type: 'string', enum: ['draft', 'published', 'archived'])),
            new OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated list of blog posts',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AdminBlogPost')),
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
    public function posts(Request $request, ListAdminBlogPostsAction $action): JsonResponse
    {
        $paginated = $action->execute(
            $request->string('status')->value() ?: null,
            $request->integer('category_id') ?: null,
            $request->string('search')->value() ?: null,
            (int) $request->input('per_page', 20)
        );

        return self::successfulResponseWithData([
            'data' => AdminBlogPostResource::collection($paginated),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    #[OA\Get(
        path: '/api/admin/blog/posts/{id}',
        summary: 'Get a blog post',
        security: [['bearerAuth' => []]],
        tags: ['Admin Blog'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(ref: '#/components/schemas/AdminBlogPost'),
            ),
            new OA\Response(response: 404, description: 'Post not found'),
        ],
    )]
    public function showPost(int $id, GetAdminBlogPostAction $action): JsonResponse
    {
        $post = $action->execute($id);

        return self::successfulResponseWithData(new AdminBlogPostResource($post, withContent: true));
    }

    #[OA\Post(
        path: '/api/admin/blog/posts',
        summary: 'Create a blog post',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['titleUk', 'titleEn', 'contentUk', 'contentEn', 'status'],
                properties: [
                    new OA\Property(property: 'titleUk', type: 'string'),
                    new OA\Property(property: 'titleEn', type: 'string'),
                    new OA\Property(property: 'contentUk', type: 'string'),
                    new OA\Property(property: 'contentEn', type: 'string'),
                    new OA\Property(property: 'excerptUk', type: 'string'),
                    new OA\Property(property: 'excerptEn', type: 'string'),
                    new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived']),
                    new OA\Property(property: 'categoryId', type: 'integer', nullable: true),
                    new OA\Property(property: 'tagIds', type: 'array', items: new OA\Items(type: 'integer')),
                    new OA\Property(property: 'coverImage', type: 'string', nullable: true),
                    new OA\Property(property: 'publishedAt', type: 'string', format: 'date', nullable: true),
                ],
            ),
        ),
        tags: ['Admin Blog'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Post created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AdminBlogPost'),
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function storePost(StoreBlogPostRequest $request, CreateBlogPostAction $action): JsonResponse
    {
        $post = $action->execute(BlogPostDto::fromRequest($request));

        return self::successfulResponseWithData(new AdminBlogPostResource($post, withContent: true), Response::HTTP_CREATED);
    }

    #[OA\Put(
        path: '/api/admin/blog/posts/{id}',
        summary: 'Update a blog post',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['titleUk', 'titleEn', 'contentUk', 'contentEn', 'status'],
                properties: [
                    new OA\Property(property: 'titleUk', type: 'string'),
                    new OA\Property(property: 'titleEn', type: 'string'),
                    new OA\Property(property: 'contentUk', type: 'string'),
                    new OA\Property(property: 'contentEn', type: 'string'),
                    new OA\Property(property: 'excerptUk', type: 'string'),
                    new OA\Property(property: 'excerptEn', type: 'string'),
                    new OA\Property(property: 'status', type: 'string', enum: ['draft', 'published', 'archived']),
                    new OA\Property(property: 'categoryId', type: 'integer', nullable: true),
                    new OA\Property(property: 'tagIds', type: 'array', items: new OA\Items(type: 'integer')),
                    new OA\Property(property: 'coverImage', type: 'string', nullable: true),
                    new OA\Property(property: 'publishedAt', type: 'string', format: 'date', nullable: true),
                ],
            ),
        ),
        tags: ['Admin Blog'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Post updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AdminBlogPost'),
            ),
            new OA\Response(response: 404, description: 'Post not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function updatePost(UpdateBlogPostRequest $request, int $id, UpdateBlogPostAction $action): JsonResponse
    {
        $post = $action->execute($id, BlogPostDto::fromRequest($request));

        return self::successfulResponseWithData(new AdminBlogPostResource($post, withContent: true));
    }

    #[OA\Delete(
        path: '/api/admin/blog/posts/{id}',
        summary: 'Delete a blog post',
        security: [['bearerAuth' => []]],
        tags: ['Admin Blog'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Post deleted successfully'),
            new OA\Response(response: 404, description: 'Post not found'),
        ],
    )]
    public function destroyPost(int $id, DeleteBlogPostAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse();
    }

    #[OA\Post(
        path: '/api/admin/blog/upload',
        summary: 'Upload a blog cover/content image',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['image'],
                    properties: [
                        new OA\Property(property: 'image', type: 'string', format: 'binary'),
                    ],
                ),
            ),
        ),
        tags: ['Admin Blog'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Image uploaded successfully',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'url', type: 'string'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function uploadImage(UploadBlogImageRequest $request, UploadBlogImageAction $action): JsonResponse
    {
        $url = $action->execute($request->file('image'));

        return self::successfulResponseWithData(['url' => $url]);
    }

    // ─── Categories ───────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/admin/blog/categories',
        summary: 'List blog categories',
        security: [['bearerAuth' => []]],
        tags: ['Admin Blog'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AdminBlogCategory')),
                    ],
                ),
            ),
        ],
    )]
    public function categories(ListAdminBlogCategoriesAction $action): JsonResponse
    {
        $categories = $action->execute();

        return self::successfulResponseWithData(AdminBlogCategoryResource::collection($categories));
    }

    #[OA\Post(
        path: '/api/admin/blog/categories',
        summary: 'Create a blog category',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nameUk', 'nameEn'],
                properties: [
                    new OA\Property(property: 'nameUk', type: 'string'),
                    new OA\Property(property: 'nameEn', type: 'string'),
                    new OA\Property(property: 'descriptionUk', type: 'string'),
                    new OA\Property(property: 'descriptionEn', type: 'string'),
                    new OA\Property(property: 'order', type: 'integer'),
                ],
            ),
        ),
        tags: ['Admin Blog'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Category created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AdminBlogCategory'),
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function storeCategory(StoreBlogCategoryRequest $request, CreateBlogCategoryAction $action): JsonResponse
    {
        $category = $action->execute(BlogCategoryDto::fromRequest($request));

        return self::successfulResponseWithData(new AdminBlogCategoryResource($category), Response::HTTP_CREATED);
    }

    #[OA\Put(
        path: '/api/admin/blog/categories/{id}',
        summary: 'Update a blog category',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nameUk', 'nameEn'],
                properties: [
                    new OA\Property(property: 'nameUk', type: 'string'),
                    new OA\Property(property: 'nameEn', type: 'string'),
                    new OA\Property(property: 'descriptionUk', type: 'string'),
                    new OA\Property(property: 'descriptionEn', type: 'string'),
                    new OA\Property(property: 'order', type: 'integer'),
                ],
            ),
        ),
        tags: ['Admin Blog'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AdminBlogCategory'),
            ),
            new OA\Response(response: 404, description: 'Category not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function updateCategory(UpdateBlogCategoryRequest $request, int $id, UpdateBlogCategoryAction $action): JsonResponse
    {
        $category = $action->execute($id, BlogCategoryDto::fromRequest($request));

        return self::successfulResponseWithData(new AdminBlogCategoryResource($category));
    }

    #[OA\Delete(
        path: '/api/admin/blog/categories/{id}',
        summary: 'Delete a blog category',
        security: [['bearerAuth' => []]],
        tags: ['Admin Blog'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Category deleted successfully'),
            new OA\Response(response: 404, description: 'Category not found'),
        ],
    )]
    public function destroyCategory(int $id, DeleteBlogCategoryAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse();
    }

    // ─── Tags ─────────────────────────────────────────────────────────────────

    #[OA\Get(
        path: '/api/admin/blog/tags',
        summary: 'List blog tags',
        security: [['bearerAuth' => []]],
        tags: ['Admin Blog'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Successful operation',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'success', type: 'boolean', example: true),
                        new OA\Property(property: 'data', type: 'array', items: new OA\Items(ref: '#/components/schemas/AdminBlogTag')),
                    ],
                ),
            ),
        ],
    )]
    public function tags(ListAdminBlogTagsAction $action): JsonResponse
    {
        $tags = $action->execute();

        return self::successfulResponseWithData(AdminBlogTagResource::collection($tags));
    }

    #[OA\Post(
        path: '/api/admin/blog/tags',
        summary: 'Create a blog tag',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nameUk', 'nameEn'],
                properties: [
                    new OA\Property(property: 'nameUk', type: 'string'),
                    new OA\Property(property: 'nameEn', type: 'string'),
                ],
            ),
        ),
        tags: ['Admin Blog'],
        responses: [
            new OA\Response(
                response: 201,
                description: 'Tag created successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AdminBlogTag'),
            ),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function storeTag(StoreBlogTagRequest $request, CreateBlogTagAction $action): JsonResponse
    {
        $tag = $action->execute(BlogTagDto::fromRequest($request));

        return self::successfulResponseWithData(new AdminBlogTagResource($tag), Response::HTTP_CREATED);
    }

    #[OA\Put(
        path: '/api/admin/blog/tags/{id}',
        summary: 'Update a blog tag',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['nameUk', 'nameEn'],
                properties: [
                    new OA\Property(property: 'nameUk', type: 'string'),
                    new OA\Property(property: 'nameEn', type: 'string'),
                ],
            ),
        ),
        tags: ['Admin Blog'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Tag updated successfully',
                content: new OA\JsonContent(ref: '#/components/schemas/AdminBlogTag'),
            ),
            new OA\Response(response: 404, description: 'Tag not found'),
            new OA\Response(response: 422, description: 'Validation error'),
        ],
    )]
    public function updateTag(UpdateBlogTagRequest $request, int $id, UpdateBlogTagAction $action): JsonResponse
    {
        $tag = $action->execute($id, BlogTagDto::fromRequest($request));

        return self::successfulResponseWithData(new AdminBlogTagResource($tag));
    }

    #[OA\Delete(
        path: '/api/admin/blog/tags/{id}',
        summary: 'Delete a blog tag',
        security: [['bearerAuth' => []]],
        tags: ['Admin Blog'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Tag deleted successfully'),
            new OA\Response(response: 404, description: 'Tag not found'),
        ],
    )]
    public function destroyTag(int $id, DeleteBlogTagAction $action): JsonResponse
    {
        $action->execute($id);

        return self::successfulResponse();
    }
}
