<?php

namespace App\Api\V1\Repositories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

class BrandRepository implements BrandRepositoryInterface
{
    public function all(): Collection
    {
        return Brand::all();
    }

    public function find(int $id): ?Brand
    {
        return Brand::find($id);
    }

    public function create(array $data): Brand
    {
        return Brand::create($data);
    }

    public function update(Brand $brand, array $data): Brand
    {
        $brand->update($data);

        return $brand;
    }

    public function delete(Brand $brand): bool
    {
        return (bool) $brand->delete();
    }

    public function getBrandsWithActiveProductsCount(array $categoryIds = []): Collection
    {
        // This feeds the public catalog's brand filter, not brand management -
        // a brand with nothing to show for the current scope would just be a
        // dead filter option, so whereHas() excludes it instead of listing it
        // at count 0 alongside withCount().
        $matchingProducts = function ($q) use ($categoryIds) {
            $q->where('status', 'active');
            if (! empty($categoryIds)) {
                $q->whereHas('categories', function ($c) use ($categoryIds) {
                    $c->whereIn('categories.id', $categoryIds);
                });
            }
        };

        return Brand::withCount(['products' => $matchingProducts])
            ->whereHas('products', $matchingProducts)
            ->orderBy('name')
            ->get();
    }

    public function findIdsBySlugs(array $slugs): array
    {
        return Brand::whereIn('slug', $slugs)->pluck('id')->all();
    }
}
