<?php

namespace App\Api\Admin\Actions\Product;

use App\Models\Product;

class BulkUpdateProductCategoryAction
{
    /**
     * Moves every given product into exactly the given category, replacing
     * whatever categories it was in before - this is a "move", not an
     * "add to".
     *
     * @param  array<int>  $ids
     * @return int Number of products actually updated.
     */
    public function execute(array $ids, int $categoryId): int
    {
        $count = 0;

        foreach (Product::whereIn('id', $ids)->get() as $product) {
            $product->categories()->sync([$categoryId]);
            // sync() writes the pivot table directly and doesn't fire a model
            // save event, so Scout's index (which embeds category_ids) needs an
            // explicit nudge to pick up the change.
            $product->searchable();
            $count++;
        }

        return $count;
    }
}
