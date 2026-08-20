<?php

namespace App\Api\V1\Actions\Blog;

use App\Models\BlogTag;
use Illuminate\Support\Collection;

class ListBlogTagsAction
{
    public function execute(): Collection
    {
        return BlogTag::whereHas('posts', fn ($q) => $q->where('status', 'published'))
            ->withCount(['posts' => fn ($q) => $q->where('status', 'published')])
            ->orderByDesc('posts_count')
            ->get()
            ->map(fn (BlogTag $tag) => [
                'id' => $tag->id,
                'slug' => $tag->slug,
                'name' => $tag->name,
                'postsCount' => $tag->posts_count,
            ]);
    }
}
