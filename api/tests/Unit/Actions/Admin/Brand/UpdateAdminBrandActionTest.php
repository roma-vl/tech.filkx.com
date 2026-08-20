<?php

namespace Tests\Unit\Actions\Admin\Brand;

use App\Api\Admin\Actions\Brand\UpdateAdminBrandAction;
use App\Api\Admin\Dto\BrandDto;
use App\Api\V1\Exceptions\BrandNotFoundException;
use App\Models\Brand;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAdminBrandActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateAdminBrandAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateAdminBrandAction::class);
    }

    public function test_execute_updates_the_brand(): void
    {
        $brand = Brand::create(['name' => 'Old', 'slug' => 'old']);
        $dto = new BrandDto(name: 'New', slug: 'new', logoPath: null, description: null);

        $updated = $this->action->execute($brand->id, $dto);

        $this->assertSame('New', $updated->name);
        $this->assertDatabaseHas('brands', ['id' => $brand->id, 'name' => 'New', 'slug' => 'new']);
    }

    public function test_execute_throws_when_the_brand_does_not_exist(): void
    {
        $dto = new BrandDto(name: 'New', slug: 'new', logoPath: null, description: null);

        $this->expectException(BrandNotFoundException::class);

        $this->action->execute(999999, $dto);
    }
}
