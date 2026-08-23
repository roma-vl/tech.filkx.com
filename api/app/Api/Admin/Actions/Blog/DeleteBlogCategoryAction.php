<?php

namespace App\Api\Admin\Actions\Blog;

use App\Models\BlogCategory;

class DeleteBlogCategoryAction
{
    public function execute(int $id): void
    {
        BlogCategory::findOrFail($id)->delete();
    }
}
