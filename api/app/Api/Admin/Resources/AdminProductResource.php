<?php

namespace App\Api\Admin\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdminProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variantsMapped = $this->variants->map(function ($variant) {
            // Read images from dimensions json
            $images = $variant->dimensions['images'] ?? [];
            if (empty($images)) {
                // Fallback to legacy single image if present
                $legacyImage = $variant->dimensions['image'] ?? null;
                if ($legacyImage) {
                    $images = [['url' => $legacyImage, 'isPrimary' => true]];
                }
            }

            $attrsMapped = $variant->attributeValues->map(function ($pav) {
                return [
                    'attributeId' => $pav->attribute_id,
                    'attributeCode' => $pav->attribute->code ?? '',
                    'attributeName' => $pav->attribute->name['uk'] ?? $pav->attribute->name['en'] ?? '',
                    'valueId' => $pav->attribute_value_id,
                    'value' => $pav->attributeValue ? ($pav->attributeValue->value['uk'] ?? $pav->attributeValue->value['value'] ?? $pav->attributeValue->value) : $pav->custom_value,
                ];
            });

            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price' => (float) $variant->price,
                'oldPrice' => $variant->old_price ? (float) $variant->old_price : null,
                'stock' => (int) $variant->stocks->sum('quantity'),
                'weight' => $variant->weight ? (float) $variant->weight : null,
                'images' => $images,
                'attributes' => $attrsMapped,
            ];
        });

        // Mapped properties for list view (take first variant's details)
        $firstVar = $variantsMapped->first();
        $primaryImage = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop';
        if ($firstVar && ! empty($firstVar['images'])) {
            foreach ($firstVar['images'] as $img) {
                if (! empty($img['isPrimary'])) {
                    $primaryImage = $img['url'];
                    break;
                }
            }
            if ($primaryImage === 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop') {
                $primaryImage = $firstVar['images'][0]['url'] ?? $primaryImage;
            }
        }

        $firstCategory = $this->categories->first();

        return [
            'id' => $this->id,
            'nameUk' => $this->name['uk'] ?? '',
            'nameEn' => $this->name['en'] ?? '',
            'descriptionUk' => $this->description['uk'] ?? '',
            'descriptionEn' => $this->description['en'] ?? '',
            'status' => $this->status,
            'isHot' => (bool) $this->is_hot,
            'isRecommended' => (bool) $this->is_recommended,
            'brandId' => $this->brand_id,
            'brandName' => $this->brand ? $this->brand->name : null,
            'categoryId' => $firstCategory ? $firstCategory->id : null,
            'categoryName' => $firstCategory ? ($firstCategory->name['uk'] ?? $firstCategory->name['en']) : null,
            'variants' => $variantsMapped,
            // Quick fields for table compatibility
            'name' => $this->name['uk'] ?? $this->name['en'] ?? '',
            'sku' => $firstVar ? $firstVar['sku'] : '',
            'category' => $firstCategory ? ($firstCategory->name['uk'] ?? $firstCategory->name['en']) : '',
            'price' => $firstVar ? $firstVar['price'] : 0,
            'discountPrice' => $firstVar ? $firstVar['oldPrice'] : null,
            'stock' => $firstVar ? $firstVar['stock'] : 0,
            'image' => $primaryImage,
            'description' => $this->description['uk'] ?? $this->description['en'] ?? '',
        ];
    }
}
