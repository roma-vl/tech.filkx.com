<?php

namespace App\Api\V1\Actions\Blog;

use App\Models\BlogPost;

class FormatBlogPostAction
{
    public function execute(BlogPost $post, bool $withContent = false): array
    {
        $data = [
            'id' => $post->id,
            'slug' => $post->slug,
            'title' => $post->title,
            'excerpt' => $post->excerpt,
            'coverImage' => $post->cover_image,
            'status' => $post->status,
            'views' => $post->views,
            'publishedAt' => $post->published_at?->toIso8601String(),
            'category' => $post->category ? ['slug' => $post->category->slug, 'name' => $post->category->name] : null,
            'author' => $post->author ? ['name' => $post->author->name] : null,
            'tags' => $post->tags->map(fn ($t) => ['slug' => $t->slug, 'name' => $t->name])->values(),
        ];

        if ($withContent) {
            $data['content'] = $post->content;
        }

        return $data;
    }
}
