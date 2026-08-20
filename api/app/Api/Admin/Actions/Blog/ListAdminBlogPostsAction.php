<?php

namespace App\Api\Admin\Actions\Blog;

use App\Models\BlogPost;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAdminBlogPostsAction
{
    public function execute(?string $status, ?int $categoryId, ?string $search, int $perPage): LengthAwarePaginator
    {
        return BlogPost::with(['category', 'author', 'tags'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($categoryId, fn ($query) => $query->where('blog_category_id', $categoryId))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->whereRaw("title->>'uk' ILIKE ?", ['%'.$search.'%'])
                        ->orWhereRaw("title->>'en' ILIKE ?", ['%'.$search.'%']);
                });
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate($perPage);
    }
}
