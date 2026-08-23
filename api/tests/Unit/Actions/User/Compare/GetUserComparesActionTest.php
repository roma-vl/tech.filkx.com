<?php

namespace Tests\Unit\Actions\User\Compare;

use App\Api\V1\Actions\User\Compare\GetUserComparesAction;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetUserComparesActionTest extends TestCase
{
    use RefreshDatabase;

    private GetUserComparesAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(GetUserComparesAction::class);
    }

    private function makeProduct(string $status = 'active'): Product
    {
        return Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => $status,
        ]);
    }

    public function test_execute_returns_the_users_compared_active_products(): void
    {
        $user = User::factory()->create();
        $product = $this->makeProduct();
        $user->compares()->attach($product->id);

        $result = $this->action->execute($user);

        $this->assertCount(1, $result);
        $this->assertSame($product->id, $result->first()->id);
    }

    public function test_execute_excludes_inactive_products(): void
    {
        $user = User::factory()->create();
        $inactive = $this->makeProduct('draft');
        $user->compares()->attach($inactive->id);

        $result = $this->action->execute($user);

        $this->assertCount(0, $result);
    }
}
