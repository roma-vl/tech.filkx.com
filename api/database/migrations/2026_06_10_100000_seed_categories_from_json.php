<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $path = database_path('data/categories.json');

        if (! file_exists($path)) {
            return;
        }

        $categories = json_decode(file_get_contents($path), true);

        if (! $categories || json_last_error() !== JSON_ERROR_NONE) {
            return;
        }

        $slugToId = [];
        $now = now();

        // Pass 1: root categories (parent_slug === null)
        foreach ($categories as $cat) {
            if ($cat['parent_slug'] !== null) {
                continue;
            }

            $existing = DB::table('categories')->where('slug', $cat['slug'])->first();

            if ($existing) {
                $slugToId[$cat['slug']] = $existing->id;
                continue;
            }

            $id = DB::table('categories')->insertGetId([
                'parent_id'  => null,
                'slug'       => $cat['slug'],
                'name'       => json_encode($cat['name'], JSON_UNESCAPED_UNICODE),
                'order'      => $cat['order'] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $slugToId[$cat['slug']] = $id;
        }

        // Pass 2: subcategories (parent_slug !== null)
        foreach ($categories as $cat) {
            if ($cat['parent_slug'] === null) {
                continue;
            }

            $parentId = $slugToId[$cat['parent_slug']] ?? null;

            if (! $parentId) {
                continue;
            }

            $existing = DB::table('categories')->where('slug', $cat['slug'])->first();

            if ($existing) {
                $slugToId[$cat['slug']] = $existing->id;
                continue;
            }

            $id = DB::table('categories')->insertGetId([
                'parent_id'  => $parentId,
                'slug'       => $cat['slug'],
                'name'       => json_encode($cat['name'], JSON_UNESCAPED_UNICODE),
                'order'      => $cat['order'] ?? 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $slugToId[$cat['slug']] = $id;
        }
    }

    public function down(): void
    {
        $path = database_path('data/categories.json');

        if (! file_exists($path)) {
            return;
        }

        $categories = json_decode(file_get_contents($path), true);

        if (! $categories) {
            return;
        }

        $slugs = array_column($categories, 'slug');

        // Delete children first to avoid FK constraint issues
        $childSlugs = array_column(
            array_filter($categories, fn ($c) => $c['parent_slug'] !== null),
            'slug'
        );
        $rootSlugs = array_column(
            array_filter($categories, fn ($c) => $c['parent_slug'] === null),
            'slug'
        );

        DB::table('categories')->whereIn('slug', $childSlugs)->delete();
        DB::table('categories')->whereIn('slug', $rootSlugs)->delete();
    }
};
