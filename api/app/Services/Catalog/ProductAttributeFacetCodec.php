<?php

namespace App\Services\Catalog;

/**
 * Encodes/decodes the tokens stored in the `attributes` Meilisearch filterable
 * field on Product documents (see Product::toSearchableArray()). Two kinds of
 * attribute assignment exist (a real AttributeValue row, or a free-text
 * `custom_value`), so tokens carry an explicit kind prefix rather than relying
 * on the value's shape - a purely numeric custom_value (e.g. "128") must never
 * be mistaken for an AttributeValue id.
 */
class ProductAttributeFacetCodec
{
    private const ATTRIBUTE_VALUE_PREFIX = 'attrval';

    private const CUSTOM_VALUE_PREFIX = 'attrcustom';

    public function encodeAttributeValue(string $attributeCode, int $attributeValueId): string
    {
        return implode(':', [self::ATTRIBUTE_VALUE_PREFIX, $attributeCode, $attributeValueId]);
    }

    public function encodeCustomValue(string $attributeCode, string $customValue): string
    {
        return implode(':', [self::CUSTOM_VALUE_PREFIX, $attributeCode, $customValue]);
    }

    /**
     * Returns the AttributeValue id encoded in the token, or null if the token
     * doesn't encode one (e.g. it's a custom-value token, or malformed).
     */
    public function decodeAttributeValueId(string $token): ?int
    {
        [$prefix, , $id] = array_pad(explode(':', $token, 3), 3, null);

        return $prefix === self::ATTRIBUTE_VALUE_PREFIX && is_numeric($id) ? (int) $id : null;
    }
}
