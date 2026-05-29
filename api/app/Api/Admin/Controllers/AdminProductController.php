<?php

namespace App\Api\Admin\Controllers;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Warehouse;
use App\Models\ProductAttributeValue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AdminProductController extends BaseApiController
{
    public function index(Request $request): JsonResponse
    {
        $products = Product::with([
            'brand', 
            'categories', 
            'variants.stocks', 
            'variants.attributeValues.attribute', 
            'variants.attributeValues.attributeValue'
        ])->get();

        $mappedProducts = $products->map(function ($product) {
            $variantsMapped = $product->variants->map(function ($variant) {
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
                    'price' => (float)$variant->price,
                    'oldPrice' => $variant->old_price ? (float)$variant->old_price : null,
                    'stock' => $variant->stocks->sum('quantity'),
                    'weight' => $variant->weight ? (float)$variant->weight : null,
                    'images' => $images,
                    'attributes' => $attrsMapped,
                ];
            });

            // Mapped properties for list view (take first variant's details)
            $firstVar = $variantsMapped->first();
            $primaryImage = 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop';
            if ($firstVar && !empty($firstVar['images'])) {
                foreach ($firstVar['images'] as $img) {
                    if (!empty($img['isPrimary'])) {
                        $primaryImage = $img['url'];
                        break;
                    }
                }
                if ($primaryImage === 'https://images.unsplash.com/photo-1523275335684-37898b6baf30?w=200&fit=crop') {
                    $primaryImage = $firstVar['images'][0]['url'] ?? $primaryImage;
                }
            }

            return [
                'id' => $product->id,
                'nameUk' => $product->name['uk'] ?? '',
                'nameEn' => $product->name['en'] ?? '',
                'descriptionUk' => $product->description['uk'] ?? '',
                'descriptionEn' => $product->description['en'] ?? '',
                'status' => $product->status,
                'brandId' => $product->brand_id,
                'brandName' => $product->brand ? $product->brand->name : null,
                'categoryId' => $product->categories->first() ? $product->categories->first()->id : null,
                'categoryName' => $product->categories->first() ? ($product->categories->first()->name['uk'] ?? $product->categories->first()->name['en']) : null,
                'variants' => $variantsMapped,
                // Quick fields for table compatibility
                'name' => $product->name['uk'] ?? $product->name['en'] ?? '',
                'sku' => $firstVar ? $firstVar['sku'] : '',
                'category' => $product->categories->first() ? ($product->categories->first()->name['uk'] ?? $product->categories->first()->name['en']) : '',
                'price' => $firstVar ? $firstVar['price'] : 0,
                'discountPrice' => $firstVar ? $firstVar['oldPrice'] : null,
                'stock' => $firstVar ? $firstVar['stock'] : 0,
                'image' => $primaryImage,
                'description' => $product->description['uk'] ?? $product->description['en'] ?? '',
            ];
        });

        return self::successfulResponseWithData($mappedProducts);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'nameUk' => 'required|string',
            'nameEn' => 'required|string',
            'descriptionUk' => 'nullable|string',
            'descriptionEn' => 'nullable|string',
            'status' => 'required|string|in:active,draft,hidden',
            'brandId' => 'nullable|exists:brands,id',
            'categoryId' => 'required|exists:categories,id',
            'variants' => 'required|array|min:1',
            'variants.*.sku' => 'required|string|unique:product_variants,sku',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.oldPrice' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.images' => 'required|array',
            'variants.*.attributes' => 'nullable|array',
        ]);

        $product = Product::create([
            'brand_id' => $request->input('brandId'),
            'slug' => Str::slug($request->input('nameEn')),
            'name' => [
                'uk' => $request->input('nameUk'),
                'en' => $request->input('nameEn'),
            ],
            'description' => [
                'uk' => $request->input('descriptionUk', ''),
                'en' => $request->input('descriptionEn', ''),
            ],
            'status' => $request->input('status'),
        ]);

        $product->categories()->sync([$request->input('categoryId')]);

        $warehouse = Warehouse::first() ?: Warehouse::create([
            'name' => 'Головний склад',
            'address' => 'Київ',
        ]);

        foreach ($request->input('variants') as $vData) {
            $variant = ProductVariant::create([
                'product_id' => $product->id,
                'sku' => $vData['sku'],
                'price' => $vData['price'],
                'old_price' => $vData['oldPrice'] ?? null,
                'weight' => $vData['weight'] ?? null,
                'dimensions' => ['images' => $vData['images']],
            ]);

            Stock::create([
                'variant_id' => $variant->id,
                'warehouse_id' => $warehouse->id,
                'quantity' => $vData['stock'],
                'reserved' => 0,
            ]);

            if (!empty($vData['attributes'])) {
                foreach ($vData['attributes'] as $attr) {
                    ProductAttributeValue::create([
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'attribute_id' => $attr['attributeId'],
                        'attribute_value_id' => $attr['valueId'] ?? null,
                        'custom_value' => $attr['value'] ?? null,
                    ]);
                }
            }
        }

        return self::successfulResponseWithData(['id' => $product->id], Response::HTTP_CREATED);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'nameUk' => 'required|string',
            'nameEn' => 'required|string',
            'descriptionUk' => 'nullable|string',
            'descriptionEn' => 'nullable|string',
            'status' => 'required|string|in:active,draft,hidden',
            'brandId' => 'nullable|exists:brands,id',
            'categoryId' => 'required|exists:categories,id',
            'variants' => 'required|array|min:1',
            'variants.*.sku' => 'required|string',
            'variants.*.price' => 'required|numeric|min:0',
            'variants.*.oldPrice' => 'nullable|numeric|min:0',
            'variants.*.stock' => 'required|integer|min:0',
            'variants.*.weight' => 'nullable|numeric|min:0',
            'variants.*.images' => 'required|array',
            'variants.*.attributes' => 'nullable|array',
        ]);

        $product->update([
            'brand_id' => $request->input('brandId'),
            'name' => [
                'uk' => $request->input('nameUk'),
                'en' => $request->input('nameEn'),
            ],
            'description' => [
                'uk' => $request->input('descriptionUk', ''),
                'en' => $request->input('descriptionEn', ''),
            ],
            'status' => $request->input('status'),
        ]);

        $product->categories()->sync([$request->input('categoryId')]);

        $warehouse = Warehouse::first() ?: Warehouse::create([
            'name' => 'Головний склад',
            'address' => 'Київ',
        ]);

        $incomingVariantIds = [];

        foreach ($request->input('variants') as $vData) {
            $variant = null;
            if (!empty($vData['id'])) {
                $variant = ProductVariant::where('product_id', $product->id)
                    ->where('id', $vData['id'])
                    ->first();
            }

            if ($variant) {
                $variant->update([
                    'sku' => $vData['sku'],
                    'price' => $vData['price'],
                    'old_price' => $vData['oldPrice'] ?? null,
                    'weight' => $vData['weight'] ?? null,
                    'dimensions' => ['images' => $vData['images']],
                ]);
            } else {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku' => $vData['sku'],
                    'price' => $vData['price'],
                    'old_price' => $vData['oldPrice'] ?? null,
                    'weight' => $vData['weight'] ?? null,
                    'dimensions' => ['images' => $vData['images']],
                ]);
            }

            $incomingVariantIds[] = $variant->id;

            // Sync stocks
            $stock = Stock::where('variant_id', $variant->id)->where('warehouse_id', $warehouse->id)->first();
            if ($stock) {
                $stock->update(['quantity' => $vData['stock']]);
            } else {
                Stock::create([
                    'variant_id' => $variant->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $vData['stock'],
                    'reserved' => 0,
                ]);
            }

            // Sync attributes
            ProductAttributeValue::where('variant_id', $variant->id)->delete();
            if (!empty($vData['attributes'])) {
                foreach ($vData['attributes'] as $attr) {
                    ProductAttributeValue::create([
                        'product_id' => $product->id,
                        'variant_id' => $variant->id,
                        'attribute_id' => $attr['attributeId'],
                        'attribute_value_id' => $attr['valueId'] ?? null,
                        'custom_value' => $attr['value'] ?? null,
                    ]);
                }
            }
        }

        // Delete variants not present in the update payload
        ProductVariant::where('product_id', $product->id)
            ->whereNotIn('id', $incomingVariantIds)
            ->delete();

        return self::successfulResponseWithData(['id' => $product->id]);
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return self::successfulResponse();
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate([
            'image' => 'required|image|max:10240', // Max 10MB
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $path = $file->store('catalog', 'public');
            $url = asset('storage/' . $path);

            return self::successfulResponseWithData([
                'url' => $url,
                'path' => $path
            ]);
        }

        return self::errorResponse('File not found', Response::HTTP_BAD_REQUEST);
    }
}
