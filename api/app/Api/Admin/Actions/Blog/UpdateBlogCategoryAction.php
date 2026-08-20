<?php

namespace App\Api\Admin\Actions\Blog;

use App\Api\Admin\Dto\BlogCategoryDto;
use App\Models\BlogCategory;

class UpdateBlogCategoryAction
{
    public function execute(int $id, BlogCategoryDto $dto): BlogCategory
    {
        $category = BlogCategory::findOrFail($id);

        $category->update($dto->toArray());

        return $category->loadCount('posts');
    }
}
