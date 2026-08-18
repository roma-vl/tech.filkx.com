<?php

namespace Tests\Unit\Actions\Admin\Product;

use App\Api\Admin\Actions\Product\ListAdminProductsAction;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListAdminProductsActionTest extends TestCase
{
    use RefreshDatabase;

    private ListAdminProductsAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ListAdminProductsAction::class);
    }

    public function test_execute_returns_every_product(): void
    {
        $active = Product::create([
            'slug' => 'active-'.uniqid(),
            'name' => ['uk' => 'А', 'en' => 'A'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
        $draft = Product::create([
            'slug' => 'draft-'.uniqid(),
            'name' => ['uk' => 'Б', 'en' => 'B'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'draft',
        ]);

        $result = $this->action->execute();

        $this->assertCount(2, $result);
        $this->assertTrue($result->contains('id', $active->id));
        $this->assertTrue($result->contains('id', $draft->id));
    }

    public function test_execute_returns_an_empty_collection_when_there_are_no_products(): void
    {
        $result = $this->action->execute();

        $this->assertCount(0, $result);
    }
}
