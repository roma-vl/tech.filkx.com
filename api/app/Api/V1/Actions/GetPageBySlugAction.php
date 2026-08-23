<?php

namespace App\Api\V1\Actions;

use App\Models\Page;

class GetPageBySlugAction
{
    public function execute(string $slug): Page
    {
        $page = Page::where('slug', $slug)
            ->where('status', 'published')
            ->first();

        if (! $page) {
            abort(404, 'Page not found.');
        }

        return $page;
    }
}
