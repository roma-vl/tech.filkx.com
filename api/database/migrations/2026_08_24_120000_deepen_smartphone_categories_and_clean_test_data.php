<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Three cleanups, bundled because they all touch the same handful of
     * smartphone-branch products:
     *
     * 1. A few depth-2 leaves still had products bound directly to them
     *    (smartphones-apple, feature-phones, smartphones-google), and two
     *    Nothing-brand phones had nowhere but the bare "smartphones" root
     *    (no per-brand child exists for Nothing) - per product direction,
     *    every product should sit at depth 3+, category depth 1/2 pages are
     *    navigation-only (child tiles + an aggregate catalog pulled from all
     *    descendants, which CategoryRepository::resolveCategoryIdsBySlug
     *    already supports since its 2026-08-24 recursive-descendant fix).
     *    Adds a "smartphones-other" depth-2 catch-all for brands with no
     *    dedicated child (currently just Nothing), plus depth-3 leaves under
     *    it and under the three depth-2 categories above.
     *
     * 2. The seeded "used-tech" catalog had 12 of its 20 "used iPhone" listings
     *    on the *current* iPhone 17 generation - unrealistic (a phone still
     *    being sold new rarely shows up used in that volume). Moves those 12
     *    into the new smartphones-apple-standard/-pro split as regular stock
     *    and strips the "Б/В ..." / "(...стан)" used-listing framing from
     *    their name. The older iPhone 16 (7) and iPhone Air (1) listings stay
     *    as used stock, matching a real store's typical used-tech mix.
     *
     * 3. 218 of 223 seeded variants carry an old_price implying a ~7% discount -
     *    a leftover "was/now" display price from the scraped source, not a
     *    real sale. Real catalogs don't have every single item discounted;
     *    clears old_price on 4 of every 5 variants (keeping product id % 5 == 0)
     *    so discounts read as the exception rather than the rule.
     */
    private const NEW_CATEGORIES = [
        ['parent_slug' => 'smartphones', 'slug' => 'smartphones-other', 'name' => ['uk' => 'Інші бренди', 'en' => 'Other Brands'], 'order' => 15],
        ['parent_slug' => 'smartphones-other', 'slug' => 'smartphones-other-nothing', 'name' => ['uk' => 'Nothing', 'en' => 'Nothing'], 'order' => 1],
        ['parent_slug' => 'smartphones-apple', 'slug' => 'smartphones-apple-standard', 'name' => ['uk' => 'iPhone', 'en' => 'iPhone'], 'order' => 1],
        ['parent_slug' => 'smartphones-apple', 'slug' => 'smartphones-apple-pro', 'name' => ['uk' => 'iPhone Pro', 'en' => 'iPhone Pro'], 'order' => 2],
        ['parent_slug' => 'feature-phones', 'slug' => 'feature-phones-nomi', 'name' => ['uk' => 'Nomi', 'en' => 'Nomi'], 'order' => 1],
        ['parent_slug' => 'feature-phones', 'slug' => 'feature-phones-maxcom', 'name' => ['uk' => 'Maxcom', 'en' => 'Maxcom'], 'order' => 2],
        ['parent_slug' => 'smartphones-google', 'slug' => 'smartphones-google-pixel-7', 'name' => ['uk' => 'Pixel 7', 'en' => 'Pixel 7'], 'order' => 1],
        ['parent_slug' => 'smartphones-google', 'slug' => 'smartphones-google-pixel-9', 'name' => ['uk' => 'Pixel 9', 'en' => 'Pixel 9'], 'order' => 2],
    ];

    // product slug => [from category slug, to category slug]
    private const RECATEGORIZATIONS = [
        'smartfon-nothing-phone-3-12-256gb-white-227330' => ['smartphones', 'smartphones-other-nothing'],
        'smartfon-nothing-phone-4a-8-128gb-black-252248' => ['smartphones', 'smartphones-other-nothing'],

        'smartfon-apple-iphone-17-256gb-esim-sage-mg4a4-228654' => ['smartphones-apple', 'smartphones-apple-standard'],
        'smartfon-apple-iphone-17-512gb-esim-sage-mg4q4-247751' => ['smartphones-apple', 'smartphones-apple-standard'],
        'smartfon-apple-iphone-17e-256gb-esim-soft-pink-mhrq4-250720' => ['smartphones-apple', 'smartphones-apple-standard'],
        'smartfon-apple-iphone-17-pro-256gb-esim-silver-mg7k4-227396' => ['smartphones-apple', 'smartphones-apple-pro'],
        'smartfon-apple-iphone-17-pro-512gb-cosmic-orange-mg8m4-224697' => ['smartphones-apple', 'smartphones-apple-pro'],
        'smartfon-apple-iphone-17-pro-512gb-esim-deep-blue-mg7q4-227146' => ['smartphones-apple', 'smartphones-apple-pro'],
        'smartfon-apple-iphone-17-pro-max-1tb-deep-blue-mfyx4-224709' => ['smartphones-apple', 'smartphones-apple-pro'],

        'telefon-nomi-i1820-black-ua-227575' => ['feature-phones', 'feature-phones-nomi'],
        'telefon-nomi-i1820-red-ua-227576' => ['feature-phones', 'feature-phones-nomi'],
        'nomi-i1850-black-ua-147160' => ['feature-phones', 'feature-phones-nomi'],
        'nomi-i1890-blue-ua-147163' => ['feature-phones', 'feature-phones-nomi'],
        'nomi-i220-red-ua-68442' => ['feature-phones', 'feature-phones-nomi'],
        'nomi-i2403-black-ua-151657' => ['feature-phones', 'feature-phones-nomi'],
        'nomi-i2403-red-ua-151659' => ['feature-phones', 'feature-phones-nomi'],
        'telefon-maxcom-mm142-black-215875' => ['feature-phones', 'feature-phones-maxcom'],
        'telefon-maxcom-mm35d-black-215881' => ['feature-phones', 'feature-phones-maxcom'],

        'google-pixel-7-8-128gb-snow-119799' => ['smartphones-google', 'smartphones-google-pixel-7'],
        'smartfon-google-pixel-9-pro-16-256gb-hazel-192612' => ['smartphones-google', 'smartphones-google-pixel-9'],
    ];

    // used-iphone-17 product slug => target (standard vs pro tier)
    private const USED_TO_NEW_IPHONES = [
        'b-v-apple-iphone-17-256gb-esim-lavender-idealnij-stan-260176' => 'smartphones-apple-standard',
        'b-v-apple-iphone-17-256gb-esim-mist-blue-idealnij-stan-260419' => 'smartphones-apple-standard',
        'b-v-apple-iphone-17-256gb-esim-sage-idealnij-stan-260435' => 'smartphones-apple-standard',
        'b-v-apple-iphone-17-pro-1tb-silver-idealnij-stan-251647' => 'smartphones-apple-pro',
        'b-v-apple-iphone-17-pro-256gb-cosmic-orange-idealnij-stan-251653' => 'smartphones-apple-pro',
        'b-v-apple-iphone-17-pro-256gb-esim-cosmic-orange-idealnij-stan-260445' => 'smartphones-apple-pro',
        'b-v-apple-iphone-17-pro-256gb-esim-deep-blue-idealnij-stan-260446' => 'smartphones-apple-pro',
        'b-v-apple-iphone-17-pro-256gb-esim-silver-idealnij-stan-260447' => 'smartphones-apple-pro',
        'b-v-apple-iphone-17-pro-512gb-esim-cosmic-orange-idealnij-stan-260448' => 'smartphones-apple-pro',
        'b-v-apple-iphone-17-pro-512gb-esim-deep-blue-idealnij-stan-260449' => 'smartphones-apple-pro',
        'b-v-apple-iphone-17-pro-max-1tb-cosmic-orange-garnij-stan-251688' => 'smartphones-apple-pro',
        'b-v-apple-iphone-17-pro-max-256gb-deep-blue-garnij-stan-251696' => 'smartphones-apple-pro',
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::NEW_CATEGORIES as $cat) {
            $parentId = DB::table('categories')->where('slug', $cat['parent_slug'])->value('id');
            if (! $parentId || DB::table('categories')->where('slug', $cat['slug'])->exists()) {
                continue;
            }

            DB::table('categories')->insert([
                'parent_id' => $parentId,
                'slug' => $cat['slug'],
                'name' => json_encode($cat['name'], JSON_UNESCAPED_UNICODE),
                'order' => $cat['order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        foreach (self::RECATEGORIZATIONS as $slug => [$fromSlug, $toSlug]) {
            $this->moveProductBySlug($slug, $fromSlug, $toSlug);
        }

        foreach (self::USED_TO_NEW_IPHONES as $slug => $toSlug) {
            $this->moveProductBySlug($slug, 'used-iphone-17', $toSlug);
            $this->stripUsedFraming($slug);
        }

        $this->clearMostDiscounts();
    }

    public function down(): void
    {
        // Discount values and the used-listing name framing aren't restored -
        // both were destructive test-data cleanups on data seeded by
        // ProductsFromSotaSeeder, not real customer-facing records; re-seed
        // to get the original scraped values back if ever needed.

        foreach (self::USED_TO_NEW_IPHONES as $slug => $fromSlug) {
            $this->moveProductBySlug($slug, $fromSlug, 'used-iphone-17');
        }

        foreach (self::RECATEGORIZATIONS as $slug => [$toSlug, $fromSlug]) {
            $this->moveProductBySlug($slug, $fromSlug, $toSlug);
        }

        foreach (array_reverse(self::NEW_CATEGORIES) as $cat) {
            DB::table('categories')->where('slug', $cat['slug'])->delete();
        }
    }

    private function stripUsedFraming(string $slug): void
    {
        $product = DB::table('products')->where('slug', $slug)->first();
        if (! $product) {
            return;
        }

        $name = json_decode($product->name, true);
        foreach ($name as $locale => $value) {
            $value = preg_replace('/^Б\/В\s+/u', '', $value);
            $value = preg_replace('/\s*\((?:Ідеальний|Гарний)\s+стан\)\s*$/u', '', $value);
            $name[$locale] = $value;
        }

        DB::table('products')->where('id', $product->id)->update([
            'name' => json_encode($name, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ]);
    }

    private function clearMostDiscounts(): void
    {
        // Postgres has no UPDATE ... JOIN syntax - a subquery stands in for it.
        DB::table('product_variants')
            ->whereNotNull('old_price')
            ->whereIn('product_id', function ($query) {
                $query->select('id')->from('products')->whereRaw('id % 5 != 0');
            })
            ->update(['old_price' => null]);
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

        // A few products (e.g. the iPhone 17 line) also carry a redundant direct
        // link to $toId's own ancestors, left over from before they had a proper
        // leaf category - products should only ever bind at the leaf, so drop those.
        $ancestorIds = [];
        $walkId = DB::table('categories')->where('id', $toId)->value('parent_id');
        while ($walkId) {
            $ancestorIds[] = $walkId;
            $walkId = DB::table('categories')->where('id', $walkId)->value('parent_id');
        }
        if ($ancestorIds) {
            DB::table('product_category')
                ->where('product_id', $productId)
                ->whereIn('category_id', $ancestorIds)
                ->delete();
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
