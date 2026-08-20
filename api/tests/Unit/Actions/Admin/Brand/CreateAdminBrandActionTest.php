<?php

namespace Tests\Unit\Actions\Admin\Brand;

use App\Api\Admin\Actions\Brand\CreateAdminBrandAction;
use App\Api\Admin\Dto\BrandDto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateAdminBrandActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateAdminBrandAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateAdminBrandAction::class);
    }

    public function test_execute_creates_the_brand(): void
    {
        $dto = new BrandDto(name: 'Acme', slug: 'acme', logoPath: 'logos/acme.png', description: 'A brand');

        $brand = $this->action->execute($dto);

        $this->assertDatabaseHas('brands', [
            'id' => $brand->id,
            'name' => 'Acme',
            'slug' => 'acme',
            'logo_path' => 'logos/acme.png',
            'description' => 'A brand',
        ]);
    }
}
