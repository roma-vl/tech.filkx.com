<?php

namespace Tests\Unit\Actions\User\Compare;

use App\Api\V1\Actions\User\Compare\ToggleUserCompareAction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ToggleUserCompareActionTest extends TestCase
{
    use RefreshDatabase;

    private ToggleUserCompareAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(ToggleUserCompareAction::class);
    }

    private function makeProduct(): Product
    {
        return Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
    }

    public function test_execute_adds_the_product_when_not_already_compared(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();

        $result = $this->action->execute($user, $product->id);

        $this->assertDatabaseHas('compares', ['user_id' => $user->id, 'product_id' => $product->id]);
        $this->assertCount(1, $result);
    }

    public function test_execute_removes_the_product_when_already_compared(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->compares()->attach($product->id);

        $result = $this->action->execute($user, $product->id);

        $this->assertDatabaseMissing('compares', ['user_id' => $user->id, 'product_id' => $product->id]);
        $this->assertCount(0, $result);
    }
}
