<?php

use App\Models\Product;

return [

    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    'prefix' => env('SCOUT_PREFIX', ''),

    'queue' => env('SCOUT_QUEUE', false),

    // Off in testing (see phpunit.xml) - RefreshDatabase wraps every test in a
    // transaction that's rolled back, never committed, so an after-commit hook
    // would never fire and nothing would ever reach the search index.
    'after_commit' => env('SCOUT_AFTER_COMMIT', true),

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    'soft_delete' => false,

    'identify' => env('SCOUT_IDENTIFY', false),

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY'),
        'index-settings' => [
            // Run `php artisan scout:sync-index-settings` after changing this.
            // See Product::toSearchableArray() for what each field holds.
            Product::class => [
                'filterableAttributes' => [
                    'status',
                    'category_ids',
                    'brand_id',
                    'price_min',
                    'price_max',
                    'variant_prices',
                    'has_discount',
                    'in_stock',
                    'attributes',
                ],
                'sortableAttributes' => [
                    'price_min',
                    'price_max',
                    'views_count',
                    'created_at',
                ],
                'searchableAttributes' => [
                    'name_uk',
                    'name_en',
                    'description_uk',
                    'description_en',
                ],
            ],
        ],
    ],

];
