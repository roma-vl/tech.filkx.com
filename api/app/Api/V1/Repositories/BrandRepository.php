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

    public function getBrandsWithActiveProductsCount(): Collection
    {
        return Brand::withCount(['products' => function ($q) {
            $q->where('status', 'active');
        }])->orderBy('name')->get();
    }
}
