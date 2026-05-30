<?php

namespace App\Api\V1\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function allWithItems(): Collection;
    
    public function findWithItems(int $id): ?Order;
    
    public function find(int $id): ?Order;
    
    public function update(Order $order, array $data): Order;
    
    public function delete(Order $order): bool;
}
