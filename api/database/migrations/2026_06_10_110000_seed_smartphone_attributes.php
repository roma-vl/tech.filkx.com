<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $path = database_path('data/attributes_smartphones.json');

        if (! file_exists($path)) {
            return;
        }

        $data = json_decode(file_get_contents($path), true);

        if (! $data || json_last_error() !== JSON_ERROR_NONE) {
            return;
        }

        $now = now();

        // Resolve category IDs from slugs
        $categoryIds = DB::table('categories')
            ->whereIn('slug', $data['category_slugs'])
            ->pluck('id', 'slug');

        foreach ($data['attributes'] as $attr) {
            // Insert or find the attribute
            $existing = DB::table('attributes')->where('code', $attr['code'])->first();

            if ($existing) {
                $attributeId = $existing->id;
            } else {
                $attributeId = DB::table('attributes')->insertGetId([
                    'code'       => $attr['code'],
                    'name'       => json_encode($attr['name'], JSON_UNESCAPED_UNICODE),
                    'type'       => $attr['type'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            // Insert attribute values (skip duplicates by checking existing)
            foreach ($attr['values'] ?? [] as $val) {
                $encodedValue = json_encode($val, JSON_UNESCAPED_UNICODE);

                $exists = DB::table('attribute_values')
                    ->where('attribute_id', $attributeId)
                    ->where('value', $encodedValue)
                    ->exists();

                if (! $exists) {
                    DB::table('attribute_values')->insert([
                        'attribute_id' => $attributeId,
                        'value'        => $encodedValue,
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]);
                }
            }

            // Link attribute to each category (category_attribute pivot)
            foreach ($categoryIds as $catId) {
                $linked = DB::table('category_attribute')
                    ->where('category_id', $catId)
                    ->where('attribute_id', $attributeId)
                    ->exists();

                if (! $linked) {
                    DB::table('category_attribute')->insert([
                        'category_id'  => $catId,
                        'attribute_id' => $attributeId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $path = database_path('data/attributes_smartphones.json');

        if (! file_exists($path)) {
            return;
        }

        $data = json_decode(file_get_contents($path), true);

        if (! $data) {
            return;
        }

        $codes = array_column($data['attributes'], 'code');

        $attributeIds = DB::table('attributes')
            ->whereIn('code', $codes)
            ->pluck('id');

        // Remove pivot links
        DB::table('category_attribute')
            ->whereIn('attribute_id', $attributeIds)
            ->delete();

        // Remove values
        DB::table('attribute_values')
            ->whereIn('attribute_id', $attributeIds)
            ->delete();

        // Remove attributes themselves
        DB::table('attributes')
            ->whereIn('code', $codes)
            ->delete();
    }
};
