<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * No seeded product had a color attribute value assigned (the shape-normalization
     * migration `2026_08_20_160000_normalize_color_attribute_value_shape` fixed how color
     * is *stored*, but nothing populated it), which left the color filter fix unverifiable
     * against real filtered results. This attaches the two existing color attribute values
     * (#e70d0d, #112aee) to three real smartphones whose names already name that color, so
     * the catalog's color swatch/filter has real data to exercise end-to-end.
     */
    private const ASSIGNMENTS = [
        // product_id => attribute_value_id
        20 => 3, // "Мобільний телефон Nomi i220 Red UA" -> #e70d0d
        17 => 3, // "Телефон Nomi i1820 Red UA" -> #e70d0d
        27 => 4, // "Смартфон Apple iPhone 17 Pro 512GB eSIM Deep Blue" -> #112aee
    ];

    public function up(): void
    {
        $colorAttributeId = DB::table('attributes')->where('type', 'color')->value('id');

        if (! $colorAttributeId) {
            return;
        }

        $now = now();

        foreach (self::ASSIGNMENTS as $productId => $attributeValueId) {
            $variantId = DB::table('product_variants')->where('product_id', $productId)->value('id');

            if (! $variantId) {
                continue;
            }

            $exists = DB::table('product_attribute_values')
                ->where('product_id', $productId)
                ->where('variant_id', $variantId)
                ->where('attribute_id', $colorAttributeId)
                ->exists();

            if ($exists) {
                continue;
            }

            DB::table('product_attribute_values')->insert([
                'product_id' => $productId,
                'variant_id' => $variantId,
                'attribute_id' => $colorAttributeId,
                'attribute_value_id' => $attributeValueId,
                'custom_value' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $colorAttributeId = DB::table('attributes')->where('type', 'color')->value('id');

        if (! $colorAttributeId) {
            return;
        }

        DB::table('product_attribute_values')
            ->whereIn('product_id', array_keys(self::ASSIGNMENTS))
            ->where('attribute_id', $colorAttributeId)
            ->delete();
    }
};
