<?php

namespace App\Api\V1\Controllers;

use App\Models\Page;
use Illuminate\Http\JsonResponse;
use App\Api\Admin\Controllers\BaseApiController;

class PageController extends BaseApiController
{
    public function show(string $slug): JsonResponse
    {
        $page = Page::where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        return self::successfulResponseWithData([
            'id' => $page->id,
            'slug' => $page->slug,
            'title' => $page->title,
            'content' => $page->content,
            'updatedAt' => $page->updated_at->toIso8601String(),
        ]);
    }
}
