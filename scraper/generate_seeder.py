#!/usr/bin/env python3
"""
Generates ProductsFromSotaSeeder.php from scraper/products.json
"""

import json
import re
import random
import os

INPUT = '/home/roma/filkx/tech.filkx.com/scraper/products.json'
OUTPUT = '/home/roma/filkx/tech.filkx.com/api/database/seeders/ProductsFromSotaSeeder.php'


def php_str(s):
    if s is None:
        return 'null'
    s = str(s)
    s = s.replace('\\', '\\\\').replace("'", "\\'")
    return f"'{s}'"


def slugify(text):
    text = text.lower()
    text = re.sub(r'[^\w\s-]', '', text)
    text = re.sub(r'[\s_]+', '-', text)
    return re.sub(r'-+', '-', text).strip('-')[:80]


def make_sku(slug):
    return 'SOTA_' + re.sub(r'-+', '_', slug.upper())[:24]


PAYMENT_IMAGE_PATTERNS = ('bank', 'privat', 'mono', 'oplata', 'payment', 'visa', 'mastercard')


def clean_description(text):
    if not text:
        return ''
    # Remove HTML artifact fragments like: Читати повністю chevron_right " data-collapse...
    text = re.sub(r'Читати повністю.*?(?=\w{4})', '', text, flags=re.DOTALL)
    text = re.sub(r'chevron_\w+', '', text)
    text = re.sub(r'data-collapse[^>]*>', '', text)
    text = re.sub(r'style="[^"]*"', '', text)
    text = re.sub(r'&nbsp;', ' ', text)
    text = re.sub(r'\s{2,}', ' ', text).strip()
    # Strip leading junk like 'Опис " ' or '" '
    text = re.sub(r'^(Опис\s*)?"?\s*', '', text).strip()
    return text[:600]


def filter_images(images):
    """Remove payment/bank images, keep only product photos."""
    result = []
    for img in images:
        img_lower = img.lower()
        if any(p in img_lower for p in PAYMENT_IMAGE_PATTERNS):
            continue
        result.append(img)
    return result[:3]


def pick_sub_slug(brand, sub_slugs):
    """Choose the best matching sub-slug for a product brand."""
    if not sub_slugs or not brand:
        return None
    brand_lower = brand.lower()
    for slug in sub_slugs:
        slug_lower = slug.lower()
        # Direct match: smartphones-apple → apple
        slug_brand = slug_lower.split('-')[-1]
        if slug_brand in brand_lower or brand_lower in slug_lower:
            return slug
    # No match — skip sub-slug rather than assign wrong one
    return None


def collect_brands(data):
    brands = set()
    for cat in data:
        for p in cat['products']:
            b = p.get('brand')
            if b and isinstance(b, str) and len(b) < 40:
                brands.add(b)
    return sorted(brands)


def generate_seeder(data):
    out = []

    def w(line=''):
        out.append(line)

    w('<?php')
    w()
    w('namespace Database\\Seeders;')
    w()
    w('use App\\Models\\Brand;')
    w('use App\\Models\\Category;')
    w('use App\\Models\\Product;')
    w('use App\\Models\\ProductVariant;')
    w('use App\\Models\\Stock;')
    w('use App\\Models\\Warehouse;')
    w('use Illuminate\\Database\\Seeder;')
    w('use Illuminate\\Support\\Facades\\DB;')
    w()
    w('class ProductsFromSotaSeeder extends Seeder')
    w('{')
    w('    public function run(): void')
    w('    {')
    w("        $warehouse = Warehouse::firstOrCreate(")
    w("            ['name' => 'Головний склад Київ'],")
    w("            ['address' => 'вул. Хрещатик 1, Київ, Україна']")
    w('        );')
    w()

    brands = collect_brands(data)
    w('        // Brands')
    w('        $brands = [];')
    for b in brands:
        slug = slugify(b)
        w(f"        $brands[{php_str(b)}] = Brand::firstOrCreate(")
        w(f"            ['slug' => {php_str(slug)}],")
        w(f"            ['name' => {php_str(b)}]")
        w('        );')
    w()

    # Category slugs
    all_slugs = set()
    for cat in data:
        all_slugs.add(cat['our_slug'])
        all_slugs.update(cat.get('sub_slugs', []))

    w('        // Load categories by slug')
    w('        $cats = [];')
    for slug in sorted(all_slugs):
        w(f"        $cats[{php_str(slug)}] = Category::where('slug', {php_str(slug)})->first();")
    w()

    # Collect products
    used_slugs = set()
    used_skus = set()
    all_products = []

    for cat in data:
        our_slug = cat['our_slug']
        sub_slugs = cat.get('sub_slugs', [])
        for p in cat['products']:
            # Slug
            raw_slug = p.get('slug') or slugify(p['name_uk'])
            # Strip .html suffix if present
            raw_slug = re.sub(r'\.html$', '', raw_slug)
            slug = raw_slug
            n = 2
            while slug in used_slugs:
                slug = f'{raw_slug}-{n}'
                n += 1
            used_slugs.add(slug)

            # SKU
            sku = make_sku(slug)[:32]
            n = 2
            while sku in used_skus:
                sku = make_sku(slug)[:30] + f'_{n}'
                n += 1
            used_skus.add(sku)

            brand_val = p.get('brand')
            sub = pick_sub_slug(brand_val, sub_slugs)
            cat_slugs = [our_slug] + ([sub] if sub else [])
            all_products.append({
                'cat_comment': f"{our_slug}",
                'brand': p.get('brand'),
                'slug': slug,
                'name_uk': p['name_uk'],
                'description_uk': clean_description(p.get('description_uk') or ''),
                'sku': sku,
                'price': p.get('price') or round(random.uniform(500, 30000), 2),
                'old_price': p.get('old_price'),
                'categories': cat_slugs,
                'images': filter_images(p.get('images') or []),
                'reviews': p.get('reviews') or [],
            })

    w('        $products = [')

    prev_comment = None
    for p in all_products:
        if p['cat_comment'] != prev_comment:
            w(f"            // === {p['cat_comment']} ===")
            prev_comment = p['cat_comment']

        w('            [')
        w(f"                'brand'       => {php_str(p['brand'])},")
        w(f"                'slug'        => {php_str(p['slug'])},")
        w(f"                'name_uk'     => {php_str(p['name_uk'])},")
        w(f"                'desc_uk'     => {php_str(p['description_uk'])},")
        w(f"                'sku'         => {php_str(p['sku'])},")
        w(f"                'price'       => {p['price']},")
        w(f"                'old_price'   => {p['old_price'] if p['old_price'] is not None else 'null'},")
        imgs_php = ', '.join(php_str(i) for i in p['images'])
        w(f"                'images'      => [{imgs_php}],")
        cats_php = ', '.join(php_str(c) for c in p['categories'])
        w(f"                'categories'  => [{cats_php}],")
        if p['reviews']:
            w(f"                'reviews'     => [")
            for r in p['reviews']:
                w(f"                    ['rating' => {r['rating']}, 'title' => {php_str(r['title'])}, 'body' => {php_str(r['body'])}],")
            w(f"                ],")
        else:
            w(f"                'reviews'     => [],")
        w('            ],')

    w('        ];')
    w()
    w('        foreach ($products as $p) {')
    w("            if (Product::where('slug', $p['slug'])->exists()) {")
    w('                continue;')
    w('            }')
    w()
    w("            $brand = $p['brand'] ? ($brands[$p['brand']] ?? null) : null;")
    w()
    w('            $product = Product::create([')
    w("                'brand_id'    => $brand?->id,")
    w("                'slug'        => $p['slug'],")
    w("                'name'        => ['uk' => $p['name_uk'], 'en' => $p['name_uk']],")
    w("                'description' => $p['desc_uk']")
    w("                    ? ['uk' => $p['desc_uk'], 'en' => $p['desc_uk']]")
    w("                    : null,")
    w("                'status'      => 'active',")
    w('            ]);')
    w()
    w('            $catIds = [];')
    w("            foreach ($p['categories'] as $slug) {")
    w("                if (!empty($cats[$slug])) {")
    w("                    $catIds[] = $cats[$slug]->id;")
    w('                }')
    w('            }')
    w("            if (!empty($catIds)) {")
    w('                $product->categories()->sync($catIds);')
    w('            }')
    w()
    w('            $variant = ProductVariant::create([')
    w("                'product_id' => $product->id,")
    w("                'sku'        => $p['sku'],")
    w("                'price'      => $p['price'],")
    w("                'old_price'  => $p['old_price'],")
    w('            ]);')
    w()
    w('            Stock::create([')
    w("                'variant_id'   => $variant->id,")
    w("                'warehouse_id' => $warehouse->id,")
    w("                'quantity'     => rand(5, 50),")
    w("                'reserved'     => 0,")
    w('            ]);')
    w()
    w("            foreach ($p['reviews'] as $r) {")
    w("                DB::table('product_reviews')->insert([")
    w("                    'product_id' => $product->id,")
    w("                    'user_id'    => null,")
    w("                    'order_id'   => null,")
    w("                    'rating'     => $r['rating'],")
    w("                    'title'      => $r['title'],")
    w("                    'body'       => $r['body'],")
    w("                    'status'     => 'approved',")
    w("                    'created_at' => now(),")
    w("                    'updated_at' => now(),")
    w('                ]);')
    w('            }')
    w('        }')
    w('    }')
    w('}')
    w()

    return '\n'.join(out)


if __name__ == '__main__':
    if not os.path.exists(INPUT):
        print(f'ERROR: {INPUT} not found. Run scrape_sota.py first.')
        exit(1)

    with open(INPUT, 'r', encoding='utf-8') as f:
        data = json.load(f)

    total = sum(len(c['products']) for c in data)
    print(f'Loaded {total} products from {len(data)} categories')

    # Quick quality check
    for cat in data:
        real = [p for p in cat['products'] if p.get('price') and p.get('price') > 100]
        print(f"  {cat['our_slug']}: {len(cat['products'])} total, {len(real)} with price")

    php = generate_seeder(data)

    with open(OUTPUT, 'w', encoding='utf-8') as f:
        f.write(php)

    print(f'\nSeeder written to {OUTPUT}')
    print(f'Lines: {len(php.splitlines())}')
