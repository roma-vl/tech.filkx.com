<?php

namespace App\Api\Admin\Actions\Blog;

use App\Models\BlogTag;
use Illuminate\Database\Eloquent\Collection;

class ListAdminBlogTagsAction
{
    /**
     * @return Collection<int, BlogTag>
     */
    public function execute(): Collection
    {
        return BlogTag::withCount('posts')->orderByDesc('id')->get();
    }
}
