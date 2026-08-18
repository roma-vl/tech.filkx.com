<?php

namespace App\Api\V1\Actions\Blog;

use App\Models\BlogPost;

class GetBlogPostAction
{
    public function __construct(
        protected FormatBlogPostAction $formatBlogPostAction
    ) {}

    public function execute(string $slug): array
    {
        $post = BlogPost::with(['category', 'author', 'tags'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        $post->increment('views');

        return $this->formatBlogPostAction->execute($post, withContent: true);
    }
}
