<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Blog\GetBlogPostAction;
use App\Api\V1\Actions\Blog\ListBlogCategoriesAction;
use App\Api\V1\Actions\Blog\ListBlogPostsAction;
use App\Api\V1\Actions\Blog\ListBlogTagsAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

class BlogController extends BaseApiController
{
    #[OA\Get(
        path: '/api/v1/blog/posts',
        summary: 'List published blog posts',
        tags: ['Blog'],
        parameters: [
            new OA\Parameter(name: 'category', description: 'Filter by category slug', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'tag', description: 'Filter by tag slug', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'search', description: 'Search post titles', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'per_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 9)),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated published blog posts',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer'),
                                            new OA\Property(property: 'slug', type: 'string'),
                                            new OA\Property(property: 'title', type: 'object'),
                                            new OA\Property(property: 'excerpt', type: 'object', nullable: true),
                                            new OA\Property(property: 'coverImage', type: 'string', nullable: true),
                                            new OA\Property(property: 'status', type: 'string'),
                                            new OA\Property(property: 'views', type: 'integer'),
                                            new OA\Property(property: 'publishedAt', type: 'string', format: 'date-time', nullable: true),
                                            new OA\Property(
                                                property: 'category',
                                                nullable: true,
                                                properties: [
                                                    new OA\Property(property: 'slug', type: 'string'),
                                                    new OA\Property(property: 'name', type: 'object'),
                                                ],
                                                type: 'object',
                                            ),
                                            new OA\Property(
                                                property: 'author',
                                                nullable: true,
                                                properties: [new OA\Property(property: 'name', type: 'string')],
                                                type: 'object',
                                            ),
                                            new OA\Property(
                                                property: 'tags',
                                                type: 'array',
                                                items: new OA\Items(
                                                    properties: [
                                                        new OA\Property(property: 'slug', type: 'string'),
                                                        new OA\Property(property: 'name', type: 'object'),
                                                    ],
                                                    type: 'object',
                                                ),
                                            ),
                                        ],
                                        type: 'object',
                                    ),
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
    public function index(Request $request, ListBlogPostsAction $action): JsonResponse
    {
        $filters = [];
        foreach (['category', 'tag', 'search'] as $key) {
            if ($request->filled($key)) {
                $filters[$key] = $request->input($key);
            }
        }

        return self::successfulResponseWithData(
            $action->execute($filters, (int) $request->input('per_page', 9))
        );
    }

    #[OA\Get(
        path: '/api/v1/blog/posts/{slug}',
        summary: 'Get a published blog post and increment its view count',
        tags: ['Blog'],
        parameters: [
            new OA\Parameter(name: 'slug', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The blog post, including its content',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'slug', type: 'string'),
                                new OA\Property(property: 'title', type: 'object'),
                                new OA\Property(property: 'excerpt', type: 'object', nullable: true),
                                new OA\Property(property: 'coverImage', type: 'string', nullable: true),
                                new OA\Property(property: 'status', type: 'string'),
                                new OA\Property(property: 'views', type: 'integer'),
                                new OA\Property(property: 'publishedAt', type: 'string', format: 'date-time', nullable: true),
                                new OA\Property(
                                    property: 'category',
                                    nullable: true,
                                    properties: [
                                        new OA\Property(property: 'slug', type: 'string'),
                                        new OA\Property(property: 'name', type: 'object'),
                                    ],
                                    type: 'object',
                                ),
                                new OA\Property(
                                    property: 'author',
                                    nullable: true,
                                    properties: [new OA\Property(property: 'name', type: 'string')],
                                    type: 'object',
                                ),
                                new OA\Property(
                                    property: 'tags',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'slug', type: 'string'),
                                            new OA\Property(property: 'name', type: 'object'),
                                        ],
                                        type: 'object',
                                    ),
                                ),
                                new OA\Property(property: 'content', type: 'object'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Post not found'),
        ],
    )]
    public function show(string $slug, GetBlogPostAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($slug));
    }

    #[OA\Get(
        path: '/api/v1/blog/categories',
        summary: 'List blog categories that have at least one published post',
        tags: ['Blog'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Blog categories with their published post counts',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'slug', type: 'string'),
                                    new OA\Property(property: 'name', type: 'object'),
                                    new OA\Property(property: 'postsCount', type: 'integer'),
                                ],
                                type: 'object',
                            ),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function categories(ListBlogCategoriesAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    #[OA\Get(
        path: '/api/v1/blog/tags',
        summary: 'List blog tags used on at least one published post',
        tags: ['Blog'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Blog tags with their published post counts, ordered by popularity',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'slug', type: 'string'),
                                    new OA\Property(property: 'name', type: 'object'),
                                    new OA\Property(property: 'postsCount', type: 'integer'),
                                ],
                                type: 'object',
                            ),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function tags(ListBlogTagsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }
}
