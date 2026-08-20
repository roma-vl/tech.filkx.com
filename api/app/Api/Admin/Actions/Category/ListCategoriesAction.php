<?php

namespace App\Api\Admin\Actions\Category;

use App\Models\Category;
use Illuminate\Database\Eloquent\Collection;

class ListCategoriesAction
{
    /**
     * @return Collection<int, Category>
     */
    public function execute(): Collection
    {
        return Category::with('parent')->get();
    }
}
