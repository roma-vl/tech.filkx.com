<?php

namespace Tests\Unit\Actions\Admin\Brand;

use App\Api\Admin\Actions\Brand\ListAdminBrandsAction;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAdminBrandsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListAdminBrandsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListAdminBrandsAction::class);
    }

    public function test_execute_returns_all_brands(): void
    {
        $brandA = Brand::create(['name' => 'Acme', 'slug' => 'acme']);
        $brandB = Brand::create(['name' => 'Zed', 'slug' => 'zed']);

        $result = $this->action->execute();

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $brandA->id));
        $this->assertTrue($result->contains('id', $brandB->id));
    }

    public function test_execute_returns_an_empty_collection_when_there_are_no_brands(): void
    {
        $result = $this->action->execute();

        $this->assertCount(0, $result);
    }
}
