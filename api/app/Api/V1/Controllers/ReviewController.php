<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\Review\CreateProductReviewAction;
use App\Api\V1\Actions\Review\ListMyReviewsAction;
use App\Api\V1\Actions\Review\ListProductReviewsAction;
use App\Api\V1\Actions\Review\UpdateProductReviewAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use OpenApi\Attributes as OA;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ReviewController extends BaseApiController
{
    #[OA\Get(
        path: '/api/v1/catalog/products/{slug}/reviews',
        summary: 'List approved reviews for a product',
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                description: 'Product slug or numeric ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Approved reviews for the product with aggregate rating stats',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'reviews',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer'),
                                            new OA\Property(property: 'rating', type: 'integer', maximum: 5, minimum: 1),
                                            new OA\Property(property: 'title', type: 'string', nullable: true),
                                            new OA\Property(property: 'body', type: 'string'),
                                            new OA\Property(property: 'photos', type: 'array', items: new OA\Items(type: 'string', format: 'uri')),
                                            new OA\Property(property: 'author', type: 'string', example: 'Анонім'),
                                            new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                        ],
                                        type: 'object',
                                    ),
                                ),
                                new OA\Property(
                                    property: 'stats',
                                    properties: [
                                        new OA\Property(property: 'count', type: 'integer', example: 12),
                                        new OA\Property(property: 'avg', type: 'number', format: 'float', example: 4.5),
                                        new OA\Property(
                                            property: 'distribution',
                                            description: 'Count of reviews per star rating, ordered 5,4,3,2,1',
                                            type: 'array',
                                            items: new OA\Items(type: 'integer'),
                                            example: [8, 3, 1, 0, 0],
                                        ),
                                    ],
                                    type: 'object',
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Product not found'),
        ],
    )]
    public function index(string $slug, ListProductReviewsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($slug));
    }

    #[OA\Post(
        path: '/api/v1/catalog/products/{slug}/reviews',
        summary: 'Submit a review for a product',
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['rating', 'body'],
                    properties: [
                        new OA\Property(property: 'rating', type: 'integer', maximum: 5, minimum: 1),
                        new OA\Property(property: 'title', type: 'string', nullable: true, maxLength: 120),
                        new OA\Property(property: 'body', type: 'string', maxLength: 2000, minLength: 10),
                        new OA\Property(
                            property: 'photos',
                            description: 'Up to 5 images, 5 MB each',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary'),
                        ),
                        new OA\Property(property: 'order_id', type: 'integer', nullable: true),
                    ],
                ),
            ),
        ),
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                description: 'Product slug or numeric ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 201,
                description: 'The created review',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'rating', type: 'integer', maximum: 5, minimum: 1),
                                new OA\Property(property: 'title', type: 'string', nullable: true),
                                new OA\Property(property: 'body', type: 'string'),
                                new OA\Property(property: 'photos', type: 'array', items: new OA\Items(type: 'string', format: 'uri')),
                                new OA\Property(property: 'author', type: 'string', example: 'Анонім'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Product not found'),
            new OA\Response(response: 422, description: 'Validation failed, or the user already reviewed this product'),
        ],
    )]
    public function store(Request $request, string $slug, CreateProductReviewAction $action): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:120',
            'body' => 'required|string|min:10|max:2000',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'order_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $validated = $validator->validated();
        $validated['photos'] = $request->file('photos', []);
        $validated['order_id'] = $request->integer('order_id') ?: null;

        try {
            $result = $action->execute($slug, $request->user(), $validated);
        } catch (UnprocessableEntityHttpException $e) {
            return self::errorResponse($e->getMessage(), 422);
        }

        return self::successfulResponseWithData($result, 201);
    }

    #[OA\Put(
        path: '/api/v1/catalog/products/{slug}/reviews',
        summary: "Update the authenticated user's review for a product",
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['rating', 'body'],
                    properties: [
                        new OA\Property(property: 'rating', type: 'integer', maximum: 5, minimum: 1),
                        new OA\Property(property: 'title', type: 'string', nullable: true, maxLength: 120),
                        new OA\Property(property: 'body', type: 'string', maxLength: 2000, minLength: 10),
                        new OA\Property(
                            property: 'existing_photos',
                            description: 'URLs of previously uploaded photos to keep',
                            type: 'array',
                            items: new OA\Items(type: 'string'),
                        ),
                        new OA\Property(
                            property: 'photos',
                            description: 'New images to add, up to 5, 5 MB each',
                            type: 'array',
                            items: new OA\Items(type: 'string', format: 'binary'),
                        ),
                    ],
                ),
            ),
        ),
        tags: ['Reviews'],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                description: 'Product slug or numeric ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'The updated review',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'rating', type: 'integer', maximum: 5, minimum: 1),
                                new OA\Property(property: 'title', type: 'string', nullable: true),
                                new OA\Property(property: 'body', type: 'string'),
                                new OA\Property(property: 'photos', type: 'array', items: new OA\Items(type: 'string', format: 'uri')),
                                new OA\Property(property: 'author', type: 'string', example: 'Анонім'),
                                new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Product not found, or the user has no review for it'),
            new OA\Response(response: 422, description: 'Validation failed'),
        ],
    )]
    public function update(Request $request, string $slug, UpdateProductReviewAction $action): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'title' => 'nullable|string|max:120',
            'body' => 'required|string|min:10|max:2000',
            'existing_photos' => 'nullable|array',
            'existing_photos.*' => 'nullable|string',
            'photos' => 'nullable|array|max:5',
            'photos.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        $validated = $validator->validated();
        $validated['photos'] = $request->file('photos', []);

        $result = $action->execute($slug, $request->user(), $validated);

        return self::successfulResponseWithData($result);
    }

    #[OA\Get(
        path: '/api/v1/user/reviews',
        summary: "List the authenticated user's reviews",
        security: [['bearerAuth' => []]],
        tags: ['Reviews'],
        responses: [
            new OA\Response(
                response: 200,
                description: "The authenticated user's reviews across all products",
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'success'),
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'product_slug', type: 'string', nullable: true),
                                    new OA\Property(property: 'product_id', type: 'integer'),
                                    new OA\Property(property: 'rating', type: 'integer', minimum: 1, maximum: 5),
                                    new OA\Property(property: 'title', type: 'string', nullable: true),
                                    new OA\Property(property: 'body', type: 'string'),
                                    new OA\Property(property: 'photos', type: 'array', items: new OA\Items(type: 'string', format: 'uri')),
                                    new OA\Property(property: 'created_at', type: 'string', format: 'date-time'),
                                ],
                                type: 'object',
                            ),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function myReviews(Request $request, ListMyReviewsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($request->user()));
    }
}
