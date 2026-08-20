<?php

namespace App\Api\V1\Repositories;

use App\Models\Promotion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PromotionRepository implements PromotionRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Promotion::with(['categories', 'products']);

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (! empty($filters['status'])) {
            $status = $filters['status'];
            if ($status === 'active') {
                $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('end_date')
                            ->orWhere('end_date', '>=', now());
                    });
            } elseif ($status === 'expired') {
                $query->where('end_date', '<', now());
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $sortBy = $filters['sortBy'] ?? 'created_at';
        $sortDir = $filters['sortDir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Promotion
    {
        return Promotion::find($id);
    }

    public function create(array $data): Promotion
    {
        return Promotion::create($data);
    }

    public function update(Promotion $promotion, array $data): Promotion
    {
        $promotion->update($data);

        return $promotion;
    }

    public function delete(Promotion $promotion): bool
    {
        return (bool) $promotion->delete();
    }

    public function activePromotions(): Collection
    {
        return Promotion::with(['categories', 'products'])
            ->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('start_date')->orWhere('start_date', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('end_date')->orWhere('end_date', '>=', now());
            })
            ->get();
    }
}
