<?php

namespace App\Api\Admin\Actions\Blog;

use App\Api\Admin\Dto\BlogPostDto;
use App\Models\BlogPost;

class UpdateBlogPostAction
{
    public function execute(int $id, BlogPostDto $dto): BlogPost
    {
        $post = BlogPost::findOrFail($id);

        $wasPublished = $post->status !== 'published' && $dto->status === 'published';

        $post->update([
            ...$dto->toArray(),
            'published_at' => $wasPublished ? now() : ($dto->publishedAt ?? $post->published_at),
        ]);

        $post->tags()->sync($dto->tagIds);

        return $post->load(['category', 'author', 'tags']);
    }
}
