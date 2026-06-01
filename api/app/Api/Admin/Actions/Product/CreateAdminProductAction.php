<?php

namespace App\Api\Admin\Actions\Product;

use App\Api\Admin\Dto\ProductDto;
use App\Api\V1\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\ProductAttributeValue;
use App\Models\ProductVariant;
use App\Models\Stock;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CreateAdminProductAction
{
    public function __construct(
        protected ProductRepositoryInterface $productRepository
    ) {}

    public function execute(ProductDto $dto): Product
    {
        return DB::transaction(function () use ($dto) {
            $slug = Str::slug($dto->nameEn) ?: 'product';
            $originalSlug = $slug;
            $count = 1;
            while ($this->productRepository->slugExists($slug)) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $product = $this->productRepository->create(array_merge(
                $dto->toArray(),
                ['slug' => $slug]
            ));

            $product->categories()->sync([$dto->categoryId]);

            $warehouse = Warehouse::first() ?: Warehouse::create([
                'name' => 'Головний склад',
                'address' => 'Київ',
            ]);

            foreach ($dto->variants as $vData) {
                $variant = ProductVariant::create([
                    'product_id' => $product->id,
                    'sku'        => $vData['sku'],
                    'price'      => $vData['price'],
                    'old_price'  => $vData['oldPrice'] ?? null,
                    'weight'     => $vData['weight'] ?? null,
                    'dimensions' => ['images' => $vData['images'] ?? []],
                ]);

                Stock::create([
                    'variant_id' => $variant->id,
                    'warehouse_id' => $warehouse->id,
                    'quantity' => $vData['stock'],
                    'reserved' => 0,
                ]);

                if (! empty($vData['attributes'])) {
                    foreach ($vData['attributes'] as $attr) {
                        ProductAttributeValue::create([
                            'product_id'         => $product->id,
                            'variant_id'         => $variant->id,
                            'attribute_id'       => $attr['attributeId'],
                            'attribute_value_id' => $attr['valueId'] ?? null,
                            'custom_value'       => $attr['value'] ?? null,
                        ]);
                    }
                }
            }

            return $product;
        });
    }
}
