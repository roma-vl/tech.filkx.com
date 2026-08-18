<?php

namespace Tests\Unit\Actions\Admin\HomeBanner;

use App\Api\Admin\Actions\HomeBanner\DeleteAdminHomeBannerAction;
use App\Api\V1\Exceptions\HomeBannerNotFoundException;
use App\Models\HomeBanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteAdminHomeBannerActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteAdminHomeBannerAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteAdminHomeBannerAction::class);
    }

    public function test_execute_deletes_the_banner(): void
    {
        $banner = HomeBanner::create([
            'title' => 'Sale',
            'image_path' => 'banners/sale.jpg',
            'link_type' => 'catalog',
        ]);

        $this->action->execute($banner->id);

        $this->assertDatabaseMissing('home_banners', ['id' => $banner->id]);
    }

    public function test_execute_throws_when_the_banner_does_not_exist(): void
    {
        $this->expectException(HomeBannerNotFoundException::class);

        $this->action->execute(999999);
    }
}
