<?php

namespace App\Api\Admin\Controllers;

use App\Models\Coupon;
use App\Models\Promotion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminMarketingController extends BaseApiController
{
    // --- Coupons ---
    public function coupons(Request $request): JsonResponse
    {
        $query = Coupon::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('code', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
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

        $sortBy = $request->input('sortBy', 'created_at');
        $sortDir = $request->input('sortDir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $coupons = $query->paginate(self::PER_PAGE);

        $items = collect($coupons->items())->map(function ($coupon) {
            return [
                'id' => $coupon->id,
                'code' => $coupon->code,
                'type' => $coupon->type,
                'amount' => $coupon->amount,
                'usageLimit' => $coupon->usage_limit,
                'usedCount' => $coupon->used_count,
                'expiresAt' => $coupon->expires_at ? $coupon->expires_at->toIso8601String() : null,
                'isActive' => $coupon->is_active,
                'createdAt' => $coupon->created_at->toIso8601String(),
            ];
        });

        return self::successfulResponseWithData([
            'data' => $items,
            'currentPage' => $coupons->currentPage(),
            'lastPage' => $coupons->lastPage(),
            'total' => $coupons->total(),
        ]);
    }

    public function storeCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'type' => 'required|string|in:percent,fixed',
            'amount' => 'required|numeric|min:0',
            'usageLimit' => 'nullable|integer|min:1',
            'expiresAt' => 'nullable|date',
            'isActive' => 'boolean',
        ]);

        $coupon = Coupon::create([
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'usage_limit' => $validated['usageLimit'] ?? null,
            'expires_at' => $validated['expiresAt'] ?? null,
            'is_active' => $validated['isActive'] ?? true,
        ]);

        return self::successfulResponseWithData($coupon);
    }

    public function updateCoupon(Request $request, $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'type' => 'required|string|in:percent,fixed',
            'amount' => 'required|numeric|min:0',
            'usageLimit' => 'nullable|integer|min:1',
            'expiresAt' => 'nullable|date',
            'isActive' => 'boolean',
        ]);

        $coupon->update([
            'code' => strtoupper($validated['code']),
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'usage_limit' => $validated['usageLimit'] ?? null,
            'expires_at' => $validated['expiresAt'] ?? null,
            'is_active' => $validated['isActive'] ?? true,
        ]);

        return self::successfulResponseWithData($coupon);
    }

    public function destroyCoupon($id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return self::successfulResponse('Coupon deleted successfully.');
    }

    // --- Promotions ---
    public function promotions(Request $request): JsonResponse
    {
        $query = Promotion::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
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

        $sortBy = $request->input('sortBy', 'created_at');
        $sortDir = $request->input('sortDir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $promotions = $query->paginate(self::PER_PAGE);

        $items = collect($promotions->items())->map(function ($promo) {
            return [
                'id' => $promo->id,
                'name' => $promo->name,
                'description' => $promo->description,
                'type' => $promo->type,
                'amount' => $promo->amount,
                'startDate' => $promo->start_date ? $promo->start_date->toIso8601String() : null,
                'endDate' => $promo->end_date ? $promo->end_date->toIso8601String() : null,
                'isActive' => $promo->is_active,
                'createdAt' => $promo->created_at->toIso8601String(),
            ];
        });

        return self::successfulResponseWithData([
            'data' => $items,
            'currentPage' => $promotions->currentPage(),
            'lastPage' => $promotions->lastPage(),
            'total' => $promotions->total(),
        ]);
    }

    public function storePromotion(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:percent,fixed',
            'amount' => 'required|numeric|min:0',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
            'isActive' => 'boolean',
        ]);

        $promotion = Promotion::create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'start_date' => $validated['startDate'] ?? null,
            'end_date' => $validated['endDate'] ?? null,
            'is_active' => $validated['isActive'] ?? true,
        ]);

        return self::successfulResponseWithData($promotion);
    }

    public function updatePromotion(Request $request, $id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|string|in:percent,fixed',
            'amount' => 'required|numeric|min:0',
            'startDate' => 'nullable|date',
            'endDate' => 'nullable|date',
            'isActive' => 'boolean',
        ]);

        $promotion->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'amount' => $validated['amount'],
            'start_date' => $validated['startDate'] ?? null,
            'end_date' => $validated['endDate'] ?? null,
            'is_active' => $validated['isActive'] ?? true,
        ]);

        return self::successfulResponseWithData($promotion);
    }

    public function destroyPromotion($id): JsonResponse
    {
        $promotion = Promotion::findOrFail($id);
        $promotion->delete();

        return self::successfulResponse('Promotion deleted successfully.');
    }
}
