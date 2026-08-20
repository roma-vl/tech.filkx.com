<?php

namespace App\Api\Admin\Actions\Blog;

use App\Api\Admin\Dto\BlogPostDto;
use App\Models\BlogPost;
use Illuminate\Support\Facades\Auth;

class CreateBlogPostAction
{
    public function __construct(
        protected GenerateUniqueBlogPostSlugAction $generateUniqueSlug
    ) {}

    public function execute(BlogPostDto $dto): BlogPost
    {
        $post = BlogPost::create([
            ...$dto->toArray(),
            'author_id' => Auth::id(),
            'slug' => $this->generateUniqueSlug->execute($dto->titleEn),
            'published_at' => $dto->status === 'published' ? ($dto->publishedAt ?? now()) : $dto->publishedAt,
        ]);

        if ($dto->tagIds) {
            $post->tags()->sync($dto->tagIds);
        }

        return $post->load(['category', 'author', 'tags']);
    }
}
