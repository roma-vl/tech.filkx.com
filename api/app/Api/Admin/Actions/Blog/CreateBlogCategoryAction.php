<?php

namespace App\Api\Admin\Actions\Blog;

use App\Api\Admin\Dto\BlogCategoryDto;
use App\Models\BlogCategory;

class CreateBlogCategoryAction
{
    public function __construct(
        protected GenerateUniqueBlogCategorySlugAction $generateUniqueSlug
    ) {}

    public function execute(BlogCategoryDto $dto): BlogCategory
    {
        return BlogCategory::create([
            ...$dto->toArray(),
            'slug' => $this->generateUniqueSlug->execute($dto->nameEn),
        ]);
    }
}
