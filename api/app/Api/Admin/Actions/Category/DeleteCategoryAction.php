<?php

namespace App\Api\Admin\Actions\Category;

use App\Models\Category;

class DeleteCategoryAction
{
    public function execute(int $id): void
    {
        Category::findOrFail($id)->delete();
    }
}
