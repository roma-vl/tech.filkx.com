<?php

namespace Tests\Unit\Actions\Admin\Brand;

use App\Api\Admin\Actions\Brand\DeleteAdminBrandAction;
use App\Api\V1\Exceptions\BrandNotFoundException;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteAdminBrandActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteAdminBrandAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteAdminBrandAction::class);
    }

    public function test_execute_deletes_the_brand(): void
    {
        $brand = Brand::create(['name' => 'Acme', 'slug' => 'acme']);

        $this->action->execute($brand->id);

        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }

    public function test_execute_throws_when_the_brand_does_not_exist(): void
    {
        $this->expectException(BrandNotFoundException::class);

        $this->action->execute(999999);
    }
}
