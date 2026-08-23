<?php

namespace App\Api\Admin\Actions\Product;

use App\Models\Product;

class BulkUpdateProductStatusAction
{
    /**
     * @param  array<int>  $ids
     * @return int Number of products actually updated.
     */
    public function execute(array $ids, string $status): int
    {
        $count = 0;

        foreach (Product::whereIn('id', $ids)->get() as $product) {
            // Update one at a time (not a single whereIn()->update()) so Eloquent's
            // saving/saved events fire per model - that's what keeps Scout's search
            // index (which filters on status) in sync.
            $product->update(['status' => $status]);
            $count++;
        }

        return $count;
    }
}
