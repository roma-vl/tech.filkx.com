<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Color attribute values were written as {"value": "#hex"} - one level more nested
     * than every other attribute type's {"uk": "...", "en": "..."} shape. Neither the
     * public catalog's filter SQL nor the frontend color swatch accounted for the extra
     * nesting, so color filtering was completely non-functional. This backfills existing
     * rows to the same {uk, en} shape (a color has no locale, so both keys hold the same
     * hex string); the write path (CreateAdminAttributeAction/UpdateAdminAttributeAction)
     * was fixed to match in the same change.
     */
    public function up(): void
    {
        DB::table('attribute_values')
            ->join('attributes', 'attributes.id', '=', 'attribute_values.attribute_id')
            ->where('attributes.type', 'color')
            ->select('attribute_values.id', 'attribute_values.value')
            ->orderBy('attribute_values.id')
            ->get()
            ->each(function ($row) {
                $value = json_decode($row->value, true);
                if (! is_array($value) || ! array_key_exists('value', $value)) {
                    return;
                }

                $hex = $value['value'];
                DB::table('attribute_values')
                    ->where('id', $row->id)
                    ->update(['value' => json_encode(['uk' => $hex, 'en' => $hex])]);
            });
    }

    public function down(): void
    {
        DB::table('attribute_values')
            ->join('attributes', 'attributes.id', '=', 'attribute_values.attribute_id')
            ->where('attributes.type', 'color')
            ->select('attribute_values.id', 'attribute_values.value')
            ->orderBy('attribute_values.id')
            ->get()
            ->each(function ($row) {
                $value = json_decode($row->value, true);
                if (! is_array($value) || ! array_key_exists('uk', $value)) {
                    return;
                }

                DB::table('attribute_values')
                    ->where('id', $row->id)
                    ->update(['value' => json_encode(['value' => $value['uk']])]);
            });
    }
};
