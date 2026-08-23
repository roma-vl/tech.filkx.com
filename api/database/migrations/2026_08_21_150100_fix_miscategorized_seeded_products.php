<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * A catalog-filter audit spot-checked product name vs. assigned category across the
     * seeded catalog and found several products filed under a category that doesn't match
     * what they are - confirmed live, not just a code smell:
     *  - Product #3 "Lenovo Legion 5 Pro 16ARH7H" (a gaming laptop) was filed under
     *    "Смартфони" (smartphones). `tablets-laptops-pc`'s children have `order` values
     *    1, 2, 4, 5... - a gap at 3 where a "Laptops" child category was clearly meant to
     *    sit but was never created, so there was nowhere correct to put it. This adds that
     *    category and moves the product into it.
     *  - Products #223-227 (used iPhone 16 Pro units) were filed under "tvs" (Телевізори),
     *    and product #228 (a used iPhone 16 Pro Max) under "lenovo" (a Lenovo-tablets
     *    sub-category of Tablets) - both clearly wrong for a phone, and inconsistent with
     *    every other used-iPhone product in the same seeded batch (e.g. #222, same model),
     *    which is correctly filed under "used-tech". Reassigned to match.
     */
    private const LAPTOPS_CATEGORY = [
        'parent_slug' => 'tablets-laptops-pc',
        'slug' => 'laptops',
        'name' => ['uk' => 'Ноутбуки', 'en' => 'Laptops'],
        'order' => 3,
    ];

    private const RECATEGORIZATIONS = [
        // product_id => [from category slug, to category slug]
        3 => ['from' => 'smartphones', 'to' => 'laptops'],
        223 => ['from' => 'tvs', 'to' => 'used-tech'],
        224 => ['from' => 'tvs', 'to' => 'used-tech'],
        225 => ['from' => 'tvs', 'to' => 'used-tech'],
        226 => ['from' => 'tvs', 'to' => 'used-tech'],
        227 => ['from' => 'tvs', 'to' => 'used-tech'],
        228 => ['from' => 'lenovo', 'to' => 'used-tech'],
    ];

    public function up(): void
    {
        $now = now();

        $parentId = DB::table('categories')->where('slug', self::LAPTOPS_CATEGORY['parent_slug'])->value('id');

        if ($parentId && ! DB::table('categories')->where('slug', self::LAPTOPS_CATEGORY['slug'])->exists()) {
            DB::table('categories')->insert([
                'parent_id' => $parentId,
                'slug' => self::LAPTOPS_CATEGORY['slug'],
                'name' => json_encode(self::LAPTOPS_CATEGORY['name'], JSON_UNESCAPED_UNICODE),
                'order' => self::LAPTOPS_CATEGORY['order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (self::RECATEGORIZATIONS as $productId => $move) {
            $this->moveProduct($productId, $move['from'], $move['to']);
        }
    }

    public function down(): void
    {
        foreach (self::RECATEGORIZATIONS as $productId => $move) {
            $this->moveProduct($productId, $move['to'], $move['from']);
        }

        DB::table('categories')->where('slug', self::LAPTOPS_CATEGORY['slug'])->delete();
    }

    private function moveProduct(int $productId, string $fromSlug, string $toSlug): void
    {
        // Products only exist via a `db:seed` run, never via migrations - a fresh
        // RefreshDatabase test database has the categories (seeded by migration) but
        // none of these product rows, so product_category's FK to products would
        // reject the insert below without this guard.
        if (! DB::table('products')->where('id', $productId)->exists()) {
            return;
        }

        $fromId = DB::table('categories')->where('slug', $fromSlug)->value('id');
        $toId = DB::table('categories')->where('slug', $toSlug)->value('id');

        if (! $fromId || ! $toId) {
            return;
        }

        DB::table('product_category')
            ->where('product_id', $productId)
            ->where('category_id', $fromId)
            ->delete();

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
