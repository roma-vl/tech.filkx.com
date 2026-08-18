<?php

namespace App\Api\V1\Controllers;

use App\Api\V1\Actions\GetPageBySlugAction;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

class PageController extends BaseApiController
{
    #[OA\Get(
        path: '/api/v1/pages/{slug}',
        summary: 'Get a published static page by slug',
        tags: ['Pages'],
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
                description: 'Static page content',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'data',
                            properties: [
                                new OA\Property(property: 'id', type: 'integer'),
                                new OA\Property(property: 'slug', type: 'string'),
                                new OA\Property(property: 'title', type: 'object', description: 'Localized title, keyed by locale'),
                                new OA\Property(property: 'content', type: 'object', description: 'Localized content, keyed by locale'),
                                new OA\Property(property: 'updatedAt', type: 'string', format: 'date-time'),
                            ],
                            type: 'object',
                        ),
                    ],
                ),
            ),
            new OA\Response(response: 404, description: 'Page not found or not published'),
        ],
    )]
    public function show(string $slug, GetPageBySlugAction $action): JsonResponse
    {
        $page = $action->execute($slug);

        return self::successfulResponseWithData([
            'id' => $page->id,
            'slug' => $page->slug,
            'title' => $page->title,
            'content' => $page->content,
            'updatedAt' => $page->updated_at->toIso8601String(),
        ]);
    }
}
