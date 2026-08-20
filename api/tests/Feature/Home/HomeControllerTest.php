<?php

namespace Tests\Feature\Home;

use App\Models\HomeBanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_data_only_returns_active_banners_in_sort_order(): void
    {
        HomeBanner::factory()->create(['title' => 'Inactive', 'is_active' => false, 'sort_order' => 0]);
        $second = HomeBanner::factory()->create(['title' => 'Second', 'is_active' => true, 'sort_order' => 2]);
        $first = HomeBanner::factory()->create(['title' => 'First', 'is_active' => true, 'sort_order' => 1]);

        $response = $this->getJson('/api/v1/catalog/home');

        $response->assertOk();
        $banners = $response->json('data.banners');

        $this->assertCount(2, $banners);
        $this->assertSame($first->id, $banners[0]['id']);
        $this->assertSame($second->id, $banners[1]['id']);
    }

    public function test_home_data_returns_empty_banners_when_none_configured(): void
    {
        $response = $this->getJson('/api/v1/catalog/home');

        $response->assertOk()
            ->assertJsonPath('data.banners', []);
    }
}
