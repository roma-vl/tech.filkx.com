<?php

namespace App\Api\V1\Controllers;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Api\Admin\Controllers\BaseApiController;

class BlogController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $query = BlogPost::with(['category', 'author', 'tags'])
            ->where('status', 'published')
            ->when($request->filled('category'), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $request->category)))
            ->when($request->filled('tag'), fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('slug', $request->tag)))
            ->when($request->filled('search'), fn ($q) => $q->where(function ($q2) use ($request) {
                $q2->whereRaw("title->>'uk' ILIKE ?", ['%'.$request->search.'%'])
                   ->orWhereRaw("title->>'en' ILIKE ?", ['%'.$request->search.'%']);
            }))
            ->orderByDesc('published_at');

        $paginated = $query->paginate($request->input('per_page', 9));

        return self::successfulResponseWithData([
            'data' => $paginated->map(fn ($post) => $this->formatPost($post)),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $post = BlogPost::with(['category', 'author', 'tags'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Increment views
        $post->increment('views');

        return self::successfulResponseWithData($this->formatPost($post, true));
    }

    public function categories(): JsonResponse
    {
        $cats = BlogCategory::withCount(['posts' => fn ($q) => $q->where('status', 'published')])
            ->orderBy('order')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'slug' => $c->slug,
                'name' => $c->name,
                'postsCount' => $c->posts_count,
            ]);

        return self::successfulResponseWithData($cats);
    }

    public function tags(): JsonResponse
    {
        $tags = BlogTag::whereHas('posts', fn ($q) => $q->where('status', 'published'))
            ->withCount(['posts' => fn ($q) => $q->where('status', 'published')])
            ->orderByDesc('posts_count')
            ->get()
            ->map(fn ($t) => [
                'id' => $t->id,
                'slug' => $t->slug,
                'name' => $t->name,
                'postsCount' => $t->posts_count,
            ]);

        return self::successfulResponseWithData($tags);
    }

    private function formatPost(BlogPost $post, bool $withContent = false): array
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
