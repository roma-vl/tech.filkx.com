<?php

namespace App\Api\V1\Repositories;

use App\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CouponRepository implements CouponRepositoryInterface
{
    public function paginate(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = Coupon::query();

        if (! empty($filters['search'])) {
            $query->where('code', 'like', "%{$filters['search']}%");
        }

        if (! empty($filters['status'])) {
            $status = $filters['status'];
            if ($status === 'active') {
                $query->where('is_active', true)
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                            ->orWhere('expires_at', '>=', now());
                    });
            } elseif ($status === 'expired') {
                $query->where('expires_at', '<', now());
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $sortBy = $filters['sortBy'] ?? 'created_at';
        $sortDir = $filters['sortDir'] ?? 'desc';
        $query->orderBy($sortBy, $sortDir);

        return $query->paginate($perPage);
    }

    public function find(int $id): ?Coupon
    {
        return Coupon::find($id);
    }

    public function findByCode(string $code): ?Coupon
    {
        return Coupon::where('code', strtoupper($code))->first();
    }

    public function create(array $data): Coupon
    {
        return Coupon::create($data);
    }

    public function update(Coupon $coupon, array $data): Coupon
    {
        $coupon->update($data);

        return $coupon;
    }

    public function delete(Coupon $coupon): bool
    {
        return (bool) $coupon->delete();
    }
}
