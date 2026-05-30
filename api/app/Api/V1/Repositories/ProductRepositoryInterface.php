<?php

namespace App\Api\V1\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    public function all(): Collection;
    
    public function find(int $id): ?Product;
    
    public function create(array $data): Product;
    
    public function update(Product $product, array $data): Product;
    
    public function delete(Product $product): bool;
    
    public function slugExists(string $slug): bool;
}
