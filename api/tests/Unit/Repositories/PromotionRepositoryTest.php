<?php

namespace Tests\Unit\Repositories;

use App\Api\V1\Repositories\PromotionRepository;
use App\Models\Promotion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PromotionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private PromotionRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = app(PromotionRepository::class);
    }

    private function makePromotion(array $overrides = []): Promotion
    {
        return Promotion::create(array_merge([
            'name' => 'Promotion '.uniqid(),
            'type' => 'percent',
            'amount' => 10,
            'is_active' => true,
        ], $overrides));
    }

    public function test_paginate_returns_all_promotions_without_filters(): void
    {
        $this->makePromotion();
        $this->makePromotion();

        $result = $this->repository->paginate([], 15);

        $this->assertSame(2, $result->total());
    }

    public function test_paginate_filters_by_search_on_name(): void
    {
        $match = $this->makePromotion(['name' => 'Summer Sale']);
        $this->makePromotion(['name' => 'Winter Sale']);

        $result = $this->repository->paginate(['search' => 'Summer'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($match->id, $result->items()[0]->id);
    }

    public function test_paginate_status_active_includes_open_ended_active_promotions(): void
    {
        $active = $this->makePromotion(['is_active' => true, 'end_date' => null]);
        $this->makePromotion(['is_active' => false, 'end_date' => null]);

        $result = $this->repository->paginate(['status' => 'active'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($active->id, $result->items()[0]->id);
    }

    public function test_paginate_status_active_excludes_promotions_past_their_end_date(): void
    {
        $this->makePromotion(['is_active' => true, 'end_date' => now()->subDays(2)]);
        $stillValid = $this->makePromotion(['is_active' => true, 'end_date' => now()->addDays(2)]);

        $result = $this->repository->paginate(['status' => 'active'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($stillValid->id, $result->items()[0]->id);
    }

    public function test_paginate_status_expired_includes_promotions_past_end_date_regardless_of_active_flag(): void
    {
        $expiredActive = $this->makePromotion(['is_active' => true, 'end_date' => now()->subDays(2)]);
        $expiredInactive = $this->makePromotion(['is_active' => false, 'end_date' => now()->subDays(2)]);
        $this->makePromotion(['is_active' => true, 'end_date' => now()->addDays(2)]);
        $this->makePromotion(['is_active' => true, 'end_date' => null]);

        $result = $this->repository->paginate(['status' => 'expired'], 15);

        $ids = collect($result->items())->pluck('id')->all();
        $this->assertCount(2, $ids);
        $this->assertContains($expiredActive->id, $ids);
        $this->assertContains($expiredInactive->id, $ids);
    }

    public function test_paginate_status_inactive_includes_only_disabled_promotions(): void
    {
        $inactive = $this->makePromotion(['is_active' => false]);
        $this->makePromotion(['is_active' => true]);

        $result = $this->repository->paginate(['status' => 'inactive'], 15);

        $this->assertSame(1, $result->total());
        $this->assertSame($inactive->id, $result->items()[0]->id);
    }

    public function test_paginate_sorts_by_the_given_column_and_direction(): void
    {
        $this->makePromotion(['name' => 'B', 'amount' => 20]);
        $this->makePromotion(['name' => 'A', 'amount' => 5]);

        $result = $this->repository->paginate(['sortBy' => 'amount', 'sortDir' => 'asc'], 15);

        $amounts = collect($result->items())->pluck('amount')->all();
        $this->assertSame([5.0, 20.0], $amounts);
    }

    public function test_paginate_respects_the_per_page_argument(): void
    {
        $this->makePromotion();
        $this->makePromotion();
        $this->makePromotion();

        $result = $this->repository->paginate([], 2);

        $this->assertCount(2, $result->items());
        $this->assertSame(3, $result->total());
    }

    public function test_find_returns_the_matching_promotion(): void
    {
        $promotion = $this->makePromotion();

        $found = $this->repository->find($promotion->id);

        $this->assertNotNull($found);
        $this->assertSame($promotion->id, $found->id);
    }

    public function test_find_returns_null_when_no_promotion_matches(): void
    {
        $this->assertNull($this->repository->find(999999));
    }

    public function test_create_persists_a_new_promotion(): void
    {
        $promotion = $this->repository->create([
            'name' => 'New Promotion',
            'type' => 'fixed',
            'amount' => 15,
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('promotions', ['id' => $promotion->id, 'name' => 'New Promotion']);
    }

    public function test_update_persists_changes_to_the_promotion(): void
    {
        $promotion = $this->makePromotion(['amount' => 10]);

        $updated = $this->repository->update($promotion, ['amount' => 25]);

        $this->assertSame(25.0, $updated->amount);
        $this->assertDatabaseHas('promotions', ['id' => $promotion->id, 'amount' => 25]);
    }

    public function test_delete_removes_the_promotion_and_returns_true(): void
    {
        $promotion = $this->makePromotion();

        $result = $this->repository->delete($promotion);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('promotions', ['id' => $promotion->id]);
    }

    public function test_active_promotions_includes_promotions_with_no_date_bounds(): void
    {
        $promotion = $this->makePromotion(['start_date' => null, 'end_date' => null]);

        $result = $this->repository->activePromotions();

        $this->assertTrue($result->contains('id', $promotion->id));
    }

    public function test_active_promotions_includes_a_promotion_within_its_date_window(): void
    {
        $promotion = $this->makePromotion([
            'start_date' => now()->subDays(2),
            'end_date' => now()->addDays(2),
        ]);

        $result = $this->repository->activePromotions();

        $this->assertTrue($result->contains('id', $promotion->id));
    }

    public function test_active_promotions_excludes_a_promotion_that_has_not_started_yet(): void
    {
        $promotion = $this->makePromotion(['start_date' => now()->addDays(2)]);

        $result = $this->repository->activePromotions();

        $this->assertFalse($result->contains('id', $promotion->id));
    }

    public function test_active_promotions_excludes_a_promotion_past_its_end_date(): void
    {
        $promotion = $this->makePromotion(['end_date' => now()->subDays(2)]);

        $result = $this->repository->activePromotions();

        $this->assertFalse($result->contains('id', $promotion->id));
    }

    public function test_active_promotions_excludes_inactive_promotions(): void
    {
        $promotion = $this->makePromotion(['is_active' => false]);

        $result = $this->repository->activePromotions();

        $this->assertFalse($result->contains('id', $promotion->id));
    }
}
