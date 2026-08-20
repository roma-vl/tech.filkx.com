<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\GetCatalogFiltersAction;
use App\Api\V1\Actions\GetProductDetailsAction;
use App\Api\V1\Actions\GetRelatedProductsAction;
use App\Api\V1\Actions\ListBrandsAction;
use App\Api\V1\Actions\ListCategoriesAction;
use App\Api\V1\Actions\ListProductsAction;
use App\Api\V1\Repositories\ProductRepository;
use App\Api\V1\Requests\ListProductsRequest;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class CatalogController extends BaseApiController
{
    #[OA\Get(
        path: '/api/v1/catalog/categories',
        summary: 'List top-level catalog categories with their children',
        tags: [
            'Catalog',
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Category tree',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'slug', type: 'string'),
                                    new OA\Property(property: 'name', type: 'object', description: 'Localized name keyed by locale (uk, en)'),
                                    new OA\Property(property: 'order', type: 'integer'),
                                    new OA\Property(property: 'children', type: 'array', items: new OA\Items(type: 'object')),
                                ],
                                type: 'object',
                            ),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function categories(ListCategoriesAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    #[OA\Get(
        path: '/api/v1/catalog/brands',
        summary: 'List brands with their active product count',
        tags: [
            'Catalog',
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Brand list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(property: 'id', type: 'integer'),
                                    new OA\Property(property: 'name', type: 'string'),
                                    new OA\Property(property: 'slug', type: 'string'),
                                    new OA\Property(property: 'logoPath', type: 'string', nullable: true),
                                    new OA\Property(property: 'productsCount', type: 'integer'),
                                ],
                                type: 'object',
                            ),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function brands(ListBrandsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    #[OA\Get(
        path: '/api/v1/catalog/filters',
        summary: 'Get the available catalog filter facets (price range and attributes)',
        tags: [
            'Catalog',
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Filter facets',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'price',
                                    properties: [
                                        new OA\Property(property: 'min', type: 'number'),
                                        new OA\Property(property: 'max', type: 'number'),
                                    ],
                                    type: 'object',
                                ),
                                new OA\Property(
                                    property: 'attributes',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            new OA\Property(property: 'id', type: 'integer'),
                                            new OA\Property(property: 'code', type: 'string'),
                                            new OA\Property(property: 'name', type: 'object'),
                                            new OA\Property(property: 'type', type: 'string'),
                                            new OA\Property(
                                                property: 'values',
                                                type: 'array',
                                                items: new OA\Items(
                                                    properties: [
                                                        new OA\Property(property: 'id', type: 'integer'),
                                                        new OA\Property(property: 'value', type: 'object'),
                                                    ],
                                                    type: 'object',
                                                ),
                                            ),
                                        ],
                                        type: 'object',
                                    ),
                                ),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function filters(GetCatalogFiltersAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute());
    }

    #[OA\Get(
        path: '/api/v1/catalog/products',
        summary: 'List active products, filtered, sorted and paginated',
        tags: [
            'Catalog',
        ],
        parameters: [
            new OA\Parameter(
                name: 'search',
                description: 'Free-text search keyword (Meilisearch, falls back to SQL LIKE on name/description)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'category',
                description: 'Category slug; includes products of its child categories',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'brand',
                description: 'Comma-separated brand slugs',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string'),
            ),
            new OA\Parameter(
                name: 'price_from',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'number'),
            ),
            new OA\Parameter(
                name: 'price_to',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'number'),
            ),
            new OA\Parameter(
                name: 'discounts',
                description: 'Only products with a variant that has an old_price set',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['true', 'false', '1', '0']),
            ),
            new OA\Parameter(
                name: 'in_stock',
                description: 'Only products with at least one variant in stock',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['true', 'false', '1', '0']),
            ),
            new OA\Parameter(
                name: 'attrs',
                description: 'EAV attribute filters, keyed by attribute code (e.g. attrs[color]=red,blue)',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'object'),
            ),
            new OA\Parameter(
                name: 'sort_by',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'string', enum: ['popularity', 'newest', 'price-asc', 'price-desc']),
            ),
            new OA\Parameter(
                name: 'page',
                in: 'query',
                required: false,
                schema: new OA\Schema(type: 'integer', example: 1),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Paginated product list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(
                                    property: 'data',
                                    type: 'array',
                                    items: new OA\Items(ref: '#/components/schemas/ProductSummary'),
                                ),
                                new OA\Property(property: 'currentPage', type: 'integer'),
                                new OA\Property(property: 'lastPage', type: 'integer'),
                                new OA\Property(property: 'perPage', type: 'integer'),
                                new OA\Property(property: 'total', type: 'integer'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function products(ListProductsRequest $request, ListProductsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($request->validated()));
    }

    #[OA\Get(
        path: '/api/v1/catalog/products/{slug}',
        summary: 'Get a single active product by slug (or numeric id)',
        tags: [
            'Catalog',
        ],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product details',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'data', ref: '#/components/schemas/ProductSummary'),
                    ],
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found or not active',
            ),
        ],
    )]
    public function product(string $slug, GetProductDetailsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($slug));
    }

    #[OA\Get(
        path: '/api/v1/catalog/products/random',
        summary: 'Get 5 random active products',
        tags: [
            'Catalog',
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Random product list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ProductSummary'),
                        ),
                    ],
                ),
            ),
        ],
    )]
    public function randomProducts(ProductRepository $productRepository): JsonResponse
    {
        $products = $productRepository->queryActive()
            ->inRandomOrder()
            ->take(5)
            ->get();

        return self::successfulResponseWithData($products);
    }

    #[OA\Get(
        path: '/api/v1/catalog/products/{slug}/related',
        summary: 'Get active products related to the given product (same category, topped up with random active products)',
        tags: [
            'Catalog',
        ],
        parameters: [
            new OA\Parameter(
                name: 'slug',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string'),
            ),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Related product list',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            type: 'array',
                            items: new OA\Items(ref: '#/components/schemas/ProductSummary'),
                        ),
                    ],
                ),
            ),
            new OA\Response(
                response: 404,
                description: 'Product not found or not active',
            ),
        ],
    )]
    public function relatedProducts(string $slug, GetRelatedProductsAction $action): JsonResponse
    {
        return self::successfulResponseWithData($action->execute($slug));
    }
}
