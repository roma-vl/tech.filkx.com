<?php

namespace App\Api\V1\Actions\Blog;

use App\Models\BlogPost;

class ListBlogPostsAction
{
    public function __construct(
        protected FormatBlogPostAction $formatBlogPostAction
    ) {}

    public function execute(array $filters, int $perPage): array
    {
        $query = BlogPost::with(['category', 'author', 'tags'])
            ->where('status', 'published')
            ->when(isset($filters['category']), fn ($q) => $q->whereHas('category', fn ($c) => $c->where('slug', $filters['category'])))
            ->when(isset($filters['tag']), fn ($q) => $q->whereHas('tags', fn ($t) => $t->where('slug', $filters['tag'])))
            ->when(isset($filters['search']), fn ($q) => $q->where(function ($q2) use ($filters) {
                $q2->whereRaw("title->>'uk' ILIKE ?", ['%'.$filters['search'].'%'])
                    ->orWhereRaw("title->>'en' ILIKE ?", ['%'.$filters['search'].'%']);
            }))
            ->orderByDesc('published_at');

        $paginated = $query->paginate($perPage);

        return [
            'data' => $paginated->map(fn (BlogPost $post) => $this->formatBlogPostAction->execute($post)),
            'meta' => [
                'total' => $paginated->total(),
                'per_page' => $paginated->perPage(),
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
            ],
        ];
    }
}
