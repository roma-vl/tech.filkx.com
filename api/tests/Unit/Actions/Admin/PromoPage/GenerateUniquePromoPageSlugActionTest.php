<?php

namespace Tests\Unit\Actions\Admin\PromoPage;

use App\Api\Admin\Actions\PromoPage\GenerateUniquePromoPageSlugAction;
use App\Models\PromoPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerateUniquePromoPageSlugActionTest extends TestCase
{
    use RefreshDatabase;

    private GenerateUniquePromoPageSlugAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GenerateUniquePromoPageSlugAction::class);
    }

    public function test_execute_slugifies_the_source_when_no_conflict_exists(): void
    {
        $this->assertSame('back-to-school', $this->action->execute('Back to School'));
    }

    public function test_execute_appends_a_numeric_suffix_when_the_slug_already_exists(): void
    {
        PromoPage::factory()->create(['slug' => 'back-to-school']);

        $this->assertSame('back-to-school-1', $this->action->execute('Back to School'));
    }

    public function test_execute_ignores_the_excluded_promo_page_when_checking_for_conflicts(): void
    {
        $promoPage = PromoPage::factory()->create(['slug' => 'back-to-school']);

        $this->assertSame(
            'back-to-school',
            $this->action->execute('Back to School', excludeId: $promoPage->id)
        );
    }

    public function test_execute_falls_back_to_a_generated_slug_when_the_source_has_no_slug(): void
    {
        $this->assertStringStartsWith('promo-', $this->action->execute('!!!'));
    }
}
