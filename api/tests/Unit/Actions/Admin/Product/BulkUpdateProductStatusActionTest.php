<?php

namespace Tests\Unit\Actions\Admin\Product;

use App\Api\Admin\Actions\Product\BulkUpdateProductStatusAction;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkUpdateProductStatusActionTest extends TestCase
{
    use RefreshDatabase;

    private BulkUpdateProductStatusAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(BulkUpdateProductStatusAction::class);
    }

    private function makeProduct(string $status = 'draft'): Product
    {
        return Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => $status,
        ]);
    }

    public function test_execute_updates_the_status_of_every_given_product(): void
    {
        $first = $this->makeProduct('draft');
        $second = $this->makeProduct('draft');
        $untouched = $this->makeProduct('draft');

        $count = $this->action->execute([$first->id, $second->id], 'active');

        $this->assertSame(2, $count);
        $this->assertSame('active', $first->fresh()->status);
        $this->assertSame('active', $second->fresh()->status);
        $this->assertSame('draft', $untouched->fresh()->status);
    }
}
