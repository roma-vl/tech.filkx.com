<?php

namespace Tests\Unit\Repositories;

use App\Api\V1\Repositories\CouponRepository;
use App\Models\Coupon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private CouponRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(CouponRepository::class);
    }

    private function makeCoupon(array $overrides = []): Coupon
    {
        return Coupon::create(array_merge([
            'code' => 'CODE-'.uniqid(),
            'type' => 'percent',
            'amount' => 10,
            'is_active' => true,
        ], $overrides));
    }

    public function test_paginate_returns_all_coupons_without_filters(): void
    {
        $this->makeCoupon();
        $this->makeCoupon();

        $result = $this->repository->paginate([], 15);

        $this->assertSame(2, $result->total());
    }

    public function test_paginate_filters_by_search_on_code(): void
    {
        $match = $this->makeCoupon(['code' => 'SUMMER10']);
        $this->makeCoupon(['code' => 'WINTER10']);

        $result = $this->repository->paginate(['search' => 'SUMMER'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($match->id, $result->items()[0]->id);
    }

    public function test_paginate_status_active_includes_non_expiring_active_coupons(): void
    {
        $active = $this->makeCoupon(['is_active' => true, 'expires_at' => null]);
        $this->makeCoupon(['is_active' => false, 'expires_at' => null]);

        $result = $this->repository->paginate(['status' => 'active'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($active->id, $result->items()[0]->id);
    }

    public function test_paginate_status_active_excludes_expired_coupons(): void
    {
        $this->makeCoupon(['is_active' => true, 'expires_at' => now()->subDays(2)]);
        $stillValid = $this->makeCoupon(['is_active' => true, 'expires_at' => now()->addDays(2)]);

        $result = $this->repository->paginate(['status' => 'active'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($stillValid->id, $result->items()[0]->id);
    }

    public function test_paginate_status_expired_includes_coupons_past_their_expiry_regardless_of_active_flag(): void
    {
        $expiredActive = $this->makeCoupon(['is_active' => true, 'expires_at' => now()->subDays(2)]);
        $expiredInactive = $this->makeCoupon(['is_active' => false, 'expires_at' => now()->subDays(2)]);
        $this->makeCoupon(['is_active' => true, 'expires_at' => now()->addDays(2)]);
        $this->makeCoupon(['is_active' => true, 'expires_at' => null]);

        $result = $this->repository->paginate(['status' => 'expired'], 15);

        $ids = collect($result->items())->pluck('id')->all();
        $this->assertCount(2, $ids);
        $this->assertContains($expiredActive->id, $ids);
        $this->assertContains($expiredInactive->id, $ids);
    }

    public function test_paginate_status_inactive_includes_only_disabled_coupons(): void
    {
        $inactive = $this->makeCoupon(['is_active' => false]);
        $this->makeCoupon(['is_active' => true]);

        $result = $this->repository->paginate(['status' => 'inactive'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($inactive->id, $result->items()[0]->id);
    }

    public function test_paginate_sorts_by_the_given_column_and_direction(): void
    {
        $this->makeCoupon(['code' => 'B-CODE', 'amount' => 20]);
        $this->makeCoupon(['code' => 'A-CODE', 'amount' => 5]);

        $result = $this->repository->paginate(['sortBy' => 'amount', 'sortDir' => 'asc'], 15);

        $amounts = collect($result->items())->pluck('amount')->all();
        $this->assertSame([5.0, 20.0], $amounts);
    }

    public function test_paginate_defaults_to_sorting_by_created_at_descending(): void
    {
        $older = $this->makeCoupon();
        $older->forceFill(['created_at' => now()->subDay()])->save();
        $newer = $this->makeCoupon();
        $newer->forceFill(['created_at' => now()])->save();

        $result = $this->repository->paginate([], 15);

        $ids = collect($result->items())->pluck('id')->all();
        $this->assertSame([$newer->id, $older->id], $ids);
    }

    public function test_paginate_respects_the_per_page_argument(): void
    {
        $this->makeCoupon();
        $this->makeCoupon();
        $this->makeCoupon();

        $result = $this->repository->paginate([], 2);

        $this->assertCount(2, $result->items());
        $this->assertSame(3, $result->total());
    }

    public function test_find_returns_the_matching_coupon(): void
    {
        $coupon = $this->makeCoupon();

        $found = $this->repository->find($coupon->id);

        $this->assertNotNull($found);
        $this->assertSame($coupon->id, $found->id);
    }

    public function test_find_returns_null_when_no_coupon_matches(): void
    {
        $this->assertNull($this->repository->find(999999));
    }

    public function test_find_by_code_matches_case_insensitively(): void
    {
        $coupon = $this->makeCoupon(['code' => 'SAVE10']);

        $found = $this->repository->findByCode('save10');

        $this->assertNotNull($found);
        $this->assertSame($coupon->id, $found->id);
    }

    public function test_find_by_code_returns_null_when_no_coupon_matches(): void
    {
        $this->assertNull($this->repository->findByCode('DOESNOTEXIST'));
    }

    public function test_create_persists_a_new_coupon(): void
    {
        $coupon = $this->repository->create([
            'code' => 'NEWCODE',
            'type' => 'fixed',
            'amount' => 15,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('coupons', ['id' => $coupon->id, 'code' => 'NEWCODE']);
    }

    public function test_update_persists_changes_to_the_coupon(): void
    {
        $coupon = $this->makeCoupon(['amount' => 10]);

        $updated = $this->repository->update($coupon, ['amount' => 25]);

        $this->assertSame(25.0, $updated->amount);
        $this->assertDatabaseHas('coupons', ['id' => $coupon->id, 'amount' => 25]);
    }

    public function test_delete_removes_the_coupon_and_returns_true(): void
    {
        $coupon = $this->makeCoupon();

        $result = $this->repository->delete($coupon);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }
}
