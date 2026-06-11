#!/usr/bin/env python3
"""
Generates BlogPostsFromSotaSeeder.php from scraper/blog_posts.json
"""

import json, re, os

INPUT  = '/home/roma/filkx/tech.filkx.com/scraper/blog_posts.json'
OUTPUT = '/home/roma/filkx/tech.filkx.com/api/database/seeders/BlogPostsFromSotaSeeder.php'

TECH_TAGS = {
    'apple': ['Apple', 'iOS', 'iPhone', 'iPad', 'MacBook', 'WWDC', 'Siri', 'AirPods'],
    'samsung': ['Samsung', 'Galaxy'],
    'xiaomi': ['Xiaomi', 'Redmi', 'POCO'],
    'nvidia': ['NVIDIA', 'RTX', 'GPU'],
    'intel': ['Intel', 'Arc'],
    'lenovo': ['Lenovo', 'Yoga'],
    'oneplus': ['OnePlus'],
    'huawei': ['Huawei'],
    'computex': ['Computex'],
    'smart-home': ['розумний дім', 'smart home'],
    'electrosamokat': ['самокат', 'scooter', 'Segway'],
    'laptop': ['ноутбук', 'laptop', 'Slim'],
    'smartphone': ['смартфон', 'smartphone'],
    'headphones': ['навушники', 'headphones', 'FreeClip', 'earbuds'],
    'ai': ['AI', 'штучний інтелект', 'Intelligence', 'Siri AI'],
    'monitor': ['монітор', 'monitor', 'Odyssey'],
    'tv': ['телевізор', 'TV', 'mini-LED'],
}


def php_str(s):
    if s is None:
        return 'null'
    s = str(s).replace('\\', '\\\\').replace("'", "\\'")
    return f"'{s}'"


def extract_tags(post):
    combined = (post['title'] + ' ' + post['excerpt']).lower()
    found = set()
    for tag_slug, keywords in TECH_TAGS.items():
        for kw in keywords:
            if kw.lower() in combined:
                found.add(tag_slug)
                break
    return list(found)[:4]


def tag_slug_to_name(slug):
    names = {
        'apple': 'Apple',
        'samsung': 'Samsung',
        'xiaomi': 'Xiaomi',
        'nvidia': 'NVIDIA',
        'intel': 'Intel',
        'lenovo': 'Lenovo',
        'oneplus': 'OnePlus',
        'huawei': 'Huawei',
        'computex': 'Computex',
        'smart-home': 'Розумний дім',
        'electrosamokat': 'Електросамокат',
        'laptop': 'Ноутбуки',
        'smartphone': 'Смартфони',
        'headphones': 'Навушники',
        'ai': 'Штучний інтелект',
        'monitor': 'Монітори',
        'tv': 'Телевізори',
    }
    return names.get(slug, slug.title())


def generate(posts):
    out = []
    w = out.append

    # Collect all tag slugs used
    all_tag_slugs = set()
    for post in posts:
        for slug in extract_tags(post):
            all_tag_slugs.add(slug)

    w('<?php')
    w('')
    w('namespace Database\\Seeders;')
    w('')
    w('use App\\Models\\BlogCategory;')
    w('use App\\Models\\BlogPost;')
    w('use App\\Models\\BlogTag;')
    w('use Illuminate\\Database\\Seeder;')
    w('use Illuminate\\Support\\Facades\\Http;')
    w('use Illuminate\\Support\\Facades\\Storage;')
    w('')
    w('class BlogPostsFromSotaSeeder extends Seeder')
    w('{')
    w('    public function run(): void')
    w('    {')
    w("        // Ensure 'news' category exists")
    w("        $category = BlogCategory::firstOrCreate(")
    w("            ['slug' => 'news'],")
    w("            ['name' => ['uk' => 'Новини', 'en' => 'News'], 'order' => 1]")
    w('        );')
    w('')
    w('        // Tags')
    w('        $tags = [];')
    for slug in sorted(all_tag_slugs):
        name_uk = tag_slug_to_name(slug)
        w(f"        $tags[{php_str(slug)}] = BlogTag::firstOrCreate(")
        w(f"            ['slug' => {php_str(slug)}],")
        w(f"            ['name' => ['uk' => {php_str(name_uk)}, 'en' => {php_str(slug.title())}]]")
        w('        );')
    w('')
    w('        $posts = [')

    for post in posts:
        tag_slugs = extract_tags(post)
        content = post['content_html'].replace("'", "\\'").replace('\\n', '\n')
        excerpt = (post['excerpt'] or '').replace("'", "\\'")
        title = post['title'].replace("'", "\\'")
        slug = post['slug']
        pub = post['published_at'] or '2026-01-01'
        thumb = post['thumbnail_url']

        w('            [')
        w(f"                'slug'          => {php_str(slug)},")
        w(f"                'title'         => {php_str(title)},")
        w(f"                'excerpt'       => {php_str(excerpt[:300])},")
        w(f"                'content'       => {php_str(post['content_html'][:8000])},")
        w(f"                'thumbnail_url' => {php_str(thumb)},")
        w(f"                'published_at'  => {php_str(pub)},")
        tag_php = ', '.join(php_str(t) for t in tag_slugs)
        w(f"                'tags'          => [{tag_php}],")
        w('            ],')

    w('        ];')
    w('')
    w('        foreach ($posts as $p) {')
    w("            if (BlogPost::where('slug', $p['slug'])->exists()) {")
    w('                continue;')
    w('            }')
    w('')
    w("            $coverImage = $this->downloadCover($p['thumbnail_url'], $p['slug']);")
    w('')
    w('            $post = BlogPost::create([')
    w("                'blog_category_id' => $category->id,")
    w("                'slug'             => $p['slug'],")
    w("                'title'            => ['uk' => $p['title'], 'en' => $p['title']],")
    w("                'excerpt'          => $p['excerpt'] ? ['uk' => $p['excerpt'], 'en' => $p['excerpt']] : null,")
    w("                'content'          => ['uk' => $p['content'], 'en' => $p['content']],")
    w("                'cover_image'      => $coverImage,")
    w("                'status'           => 'published',")
    w("                'published_at'     => $p['published_at'],")
    w('            ]);')
    w('')
    w("            $tagIds = [];")
    w("            foreach ($p['tags'] as $slug) {")
    w("                if (!empty($tags[$slug])) {")
    w("                    $tagIds[] = $tags[$slug]->id;")
    w('                }')
    w('            }')
    w("            if (!empty($tagIds)) {")
    w("                $post->tags()->sync($tagIds);")
    w('            }')
    w('        }')
    w('    }')
    w('')
    w('    private function downloadCover(string $url, string $slug): ?string')
    w('    {')
    w("        if (empty($url)) return null;")
    w('')
    w("        $url = preg_replace('/-\\d+x\\d+\\.(webp|jpg|jpeg|png)$/', '-600x600.$1', $url);")
    w("        $ext  = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'webp';")
    w("        $name = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_FILENAME);")
    w("        $name = preg_replace('/-\\d+x\\d+$/', '', $name);")
    w("        $path = \"blog/{$slug}.{$ext}\";")
    w('')
    w("        if (Storage::disk('public')->exists($path)) {")
    w("            return Storage::disk('public')->url($path);")
    w('        }')
    w('')
    w('        try {')
    w('            $response = Http::timeout(20)')
    w("                ->withHeaders(['User-Agent' => 'Mozilla/5.0', 'Referer' => 'https://sota.store/'])")
    w('                ->get($url);')
    w('')
    w("            if ($response->successful() && str_starts_with($response->header('Content-Type') ?? '', 'image/')) {")
    w("                Storage::disk('public')->put($path, $response->body());")
    w("                return Storage::disk('public')->url($path);")
    w('            }')
    w('        } catch (\\Throwable) {}')
    w('')
    w('        return $url;')
    w('    }')
    w('}')
    w('')

    return '\n'.join(out)


if __name__ == '__main__':
    with open(INPUT) as f:
        posts = json.load(f)

    print(f'Loaded {len(posts)} posts')
    for p in posts:
        tags = extract_tags(p)
        print(f"  {p['slug'][:55]:55s} tags={tags}")

    php = generate(posts)
    with open(OUTPUT, 'w', encoding='utf-8') as f:
        f.write(php)

    print(f'\nSeeder → {OUTPUT} ({len(php.splitlines())} lines)')
