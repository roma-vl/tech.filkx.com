<?php

namespace App\Api\Admin\Actions\Blog;

use App\Api\Admin\Dto\BlogTagDto;
use App\Models\BlogTag;

class CreateBlogTagAction
{
    public function __construct(
        protected GenerateUniqueBlogTagSlugAction $generateUniqueSlug
    ) {}

    public function execute(BlogTagDto $dto): BlogTag
    {
        return BlogTag::create([
            ...$dto->toArray(),
            'slug' => $this->generateUniqueSlug->execute($dto->nameEn),
        ]);
    }
}
