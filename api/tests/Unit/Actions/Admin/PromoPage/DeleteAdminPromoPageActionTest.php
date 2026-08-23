<?php

namespace Tests\Unit\Actions\Admin\PromoPage;

use App\Api\Admin\Actions\PromoPage\DeleteAdminPromoPageAction;
use App\Api\V1\Exceptions\PromoPageNotFoundException;
use App\Models\PromoPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteAdminPromoPageActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteAdminPromoPageAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteAdminPromoPageAction::class);
    }

    public function test_execute_deletes_the_promo_page(): void
    {
        $promoPage = PromoPage::factory()->create();

        $this->action->execute($promoPage->id);

        $this->assertDatabaseMissing('promo_pages', ['id' => $promoPage->id]);
    }

    public function test_execute_throws_when_the_promo_page_does_not_exist(): void
    {
        $this->expectException(PromoPageNotFoundException::class);

        $this->action->execute(999999);
    }
}
