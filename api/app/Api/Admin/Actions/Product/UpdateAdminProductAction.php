<?php

namespace App\Api\Admin\Actions\Product;

use App\Api\Admin\Dto\ProductDto;
use App\Api\V1\Exceptions\ProductNotFoundException;
use App\Api\V1\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;

class UpdateAdminProductAction
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function execute(int $id, ProductDto $dto): Product
    {
        $product = $this->productRepository->find($id);

        if (! $product) {
            throw new ProductNotFoundException;
        }

        return DB::transaction(function () use ($product, $dto) {
            $this->productRepository->update($product, $dto->toArray());

            $product->categories()->sync([$dto->categoryId]);

            $warehouse = Warehouse::first() ?: Warehouse::create([
                'name' => 'Головний склад',
                'address' => 'Київ',
            ]);

            $incomingVariantIds = [];

            foreach ($dto->variants as $vData) {
                $variant = null;
                if (! empty($vData['id'])) {
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
                        'dimensions' => ['images' => $vData['images'] ?? []],
                    ]);
                } else {
                    $variant = ProductVariant::create([
                        'product_id' => $product->id,
                        'sku' => $vData['sku'],
                        'price' => $vData['price'],
                        'old_price' => $vData['oldPrice'] ?? null,
                        'weight' => $vData['weight'] ?? null,
                        'dimensions' => ['images' => $vData['images'] ?? []],
                    ]);
                }

                $incomingVariantIds[] = $variant->id;

                // Sync stocks
                $stock = Stock::where('variant_id', $variant->id)
                    ->where('warehouse_id', $warehouse->id)
                    ->first();
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
                if (! empty($vData['attributes'])) {
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

            return $product;
        });
    }
}
