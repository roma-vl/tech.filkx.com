<?php

namespace App\Api\V1\Repositories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;

class BrandRepository
{
    public function getBrandsWithActiveProductsCount(): Collection
    {
        return Brand::withCount(['products' => function ($q) {
            $q->where('status', 'active');
        }])->orderBy('name')->get();
    }
}
