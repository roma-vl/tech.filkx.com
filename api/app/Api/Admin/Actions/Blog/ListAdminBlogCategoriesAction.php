<?php

namespace App\Api\Admin\Actions\Blog;

use App\Models\BlogCategory;
use Illuminate\Database\Eloquent\Collection;

class ListAdminBlogCategoriesAction
{
    /**
     * @return Collection<int, BlogCategory>
     */
    public function execute(): Collection
    {
        return BlogCategory::withCount('posts')->orderBy('order')->get();
    }
}
