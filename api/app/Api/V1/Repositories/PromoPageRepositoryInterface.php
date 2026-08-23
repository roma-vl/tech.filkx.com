<?php

namespace App\Api\V1\Repositories;

use App\Models\PromoPage;
use Illuminate\Database\Eloquent\Collection;

interface PromoPageRepositoryInterface
{
    public function all(): Collection;

    public function find(int $id): ?PromoPage;

    public function findActiveBySlugWithProducts(string $slug): ?PromoPage;

    public function create(array $data): PromoPage;

    public function update(PromoPage $promoPage, array $data): PromoPage;

    public function delete(PromoPage $promoPage): bool;

    public function syncProducts(PromoPage $promoPage, array $productIds): void;
}
