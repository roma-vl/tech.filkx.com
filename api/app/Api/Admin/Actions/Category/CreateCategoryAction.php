<?php

namespace App\Api\Admin\Actions\Category;

use App\Api\Admin\Dto\CategoryDto;
use App\Models\Category;

class CreateCategoryAction
{
    public function __construct(
        protected GenerateUniqueCategorySlugAction $generateUniqueSlug
    ) {}

    public function execute(CategoryDto $dto): Category
    {
        return Category::create([
            ...$dto->toArray(),
            'slug' => $this->generateUniqueSlug->execute($dto->nameEn),
        ]);
    }
}
