<?php

namespace App\Api\Admin\Actions\Category;

use App\Api\Admin\Dto\CategoryDto;
use App\Models\Category;

class UpdateCategoryAction
{
    public function execute(int $id, CategoryDto $dto): Category
    {
        $category = Category::findOrFail($id);

        $category->update($dto->toArray());

        return $category;
    }
}
