<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * 2026_06_10_100000_seed_categories_from_json only inserts each categories.json
     * entry once, at the moment it first runs - environments that migrated before
     * categories.json grew its 14 smartphone-brand children (+"audio") never got
     * them, since a migration marked as already run doesn't re-execute. That silently
     * broke 2026_08_24_090500's move for every product targeting one of those missing
     * categories (its per-row guard skips instead of failing, so the migration still
     * reported success) - this syncs any categories.json entries still missing, then
     * retries exactly those stalled moves.
     */
    private const PENDING_REASSIGNMENTS = [
        'google-pixel-7-8-128gb-snow-119799' => 'smartphones-google',
        'smartfon-google-pixel-9-pro-16-256gb-hazel-192612' => 'smartphones-google',
        'telefon-maxcom-mm142-black-215875' => 'feature-phones',
        'telefon-maxcom-mm35d-black-215881' => 'feature-phones',
        'telefon-nomi-i1820-black-ua-227575' => 'feature-phones',
        'telefon-nomi-i1820-red-ua-227576' => 'feature-phones',
        'nomi-i1850-black-ua-147160' => 'feature-phones',
        'nomi-i1890-blue-ua-147163' => 'feature-phones',
        'nomi-i220-red-ua-68442' => 'feature-phones',
        'nomi-i2403-black-ua-151657' => 'feature-phones',
        'nomi-i2403-red-ua-151659' => 'feature-phones',
    ];

    public function up(): void
    {
        $this->syncMissingCategories();

        foreach (self::PENDING_REASSIGNMENTS as $slug => $childSlug) {
            $product = DB::table('products')->where('slug', $slug)->first();
            if (! $product) {
                continue;
            }

            $currentParentSlug = DB::table('product_category')
                ->join('categories', 'categories.id', '=', 'product_category.category_id')
                ->where('product_category.product_id', $product->id)
                ->whereNull('categories.parent_id')
                ->value('categories.slug');

            $this->moveProductBySlug($slug, $currentParentSlug, $childSlug);
        }
    }

    public function down(): void
    {
        foreach (self::PENDING_REASSIGNMENTS as $slug => $childSlug) {
            $product = DB::table('products')->where('slug', $slug)->first();
            if (! $product) {
                continue;
            }

            $this->moveProductBySlug($slug, $childSlug, 'smartphones');
        }

        // Newly-synced categories are left in place - other environments may already
        // depend on them (they exist on production independently of this migration),
        // so deleting them here isn't safe to assume.
    }

    private function syncMissingCategories(): void
    {
        $path = database_path('data/categories.json');
        if (! file_exists($path)) {
            return;
        }

        $categories = json_decode(file_get_contents($path), true);
        if (! $categories || json_last_error() !== JSON_ERROR_NONE) {
            return;
        }

        $now = now();
        $slugToId = DB::table('categories')->pluck('id', 'slug')->all();

        foreach ([null, 'not-null'] as $pass) {
            foreach ($categories as $cat) {
                $isRoot = $cat['parent_slug'] === null;
                if (($pass === null) !== $isRoot) {
                    continue;
                }

                if (isset($slugToId[$cat['slug']])) {
                    continue;
                }

                $parentId = $isRoot ? null : ($slugToId[$cat['parent_slug']] ?? null);
                if (! $isRoot && ! $parentId) {
                    continue;
                }

                $id = DB::table('categories')->insertGetId([
                    'parent_id' => $parentId,
                    'slug' => $cat['slug'],
                    'name' => json_encode($cat['name'], JSON_UNESCAPED_UNICODE),
                    'order' => $cat['order'] ?? 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $slugToId[$cat['slug']] = $id;
            }
        }
    }

    private function moveProductBySlug(string $productSlug, ?string $fromSlug, ?string $toSlug): void
    {
        $productId = DB::table('products')->where('slug', $productSlug)->value('id');

        if (! $productId || ! $toSlug) {
            return;
        }

        $toId = DB::table('categories')->where('slug', $toSlug)->value('id');
        if (! $toId) {
            return;
        }

        if ($fromSlug) {
            $fromId = DB::table('categories')->where('slug', $fromSlug)->value('id');
            if ($fromId) {
                DB::table('product_category')
                    ->where('product_id', $productId)
                    ->where('category_id', $fromId)
                    ->delete();
            }
        }

        $alreadyLinked = DB::table('product_category')
            ->where('product_id', $productId)
            ->where('category_id', $toId)
            ->exists();

        if (! $alreadyLinked) {
            DB::table('product_category')->insert([
                'product_id' => $productId,
                'category_id' => $toId,
            ]);
        }
    }
};
