<?php

namespace App\Api\Admin\Actions\System;

use App\Models\Product;

class RebuildSearchIndexAction
{
    /**
     * Wipes and fully rebuilds the Meilisearch product index from the database -
     * an escape hatch for when the index has drifted from Postgres (e.g. a data
     * fix that wrote through DB::table() instead of Eloquent, which doesn't fire
     * the model events Scout relies on to stay in sync automatically).
     *
     * @return array{indexed: int}
     */
    public function execute(): array
    {
        Product::removeAllFromSearch();
        Product::makeAllSearchable();

        return ['indexed' => Product::count()];
    }
}
