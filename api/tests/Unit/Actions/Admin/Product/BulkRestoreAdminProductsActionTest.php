<?php

namespace Tests\Unit\Actions\Admin\Product;

use App\Api\Admin\Actions\Product\BulkRestoreAdminProductsAction;
use App\Api\V1\Repositories\ProductRepository;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulkRestoreAdminProductsActionTest extends TestCase
{
    use RefreshDatabase;

    private BulkRestoreAdminProductsAction $action;

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(BulkRestoreAdminProductsAction::class);
        $this->repository = app(ProductRepository::class);
    }

    private function makeProduct(string $slug): Product
    {
        return Product::create([
            'slug' => $slug,
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
    }

    public function test_execute_restores_every_given_trashed_product(): void
    {
        $first = $this->makeProduct('first');
        $second = $this->makeProduct('second');
        $this->repository->delete($first);
        $this->repository->delete($second);

        $result = $this->action->execute([$first->id, $second->id]);

        $this->assertSame(2, $result['restored']);
        $this->assertSame([], $result['failed']);
        $this->assertNotNull(Product::find($first->id));
        $this->assertNotNull(Product::find($second->id));
    }

    public function test_execute_reports_ids_that_do_not_exist_as_failed(): void
    {
        $result = $this->action->execute([999999]);

        $this->assertSame(0, $result['restored']);
        $this->assertSame([999999], $result['failed']);
    }

    public function test_execute_reports_a_slug_conflict_as_failed_without_stopping_the_rest(): void
    {
        $conflicted = $this->makeProduct('taken-slug');
        $this->repository->delete($conflicted);
        $this->makeProduct('taken-slug');
        $restorable = $this->makeProduct('free-slug');
        $this->repository->delete($restorable);

        $result = $this->action->execute([$conflicted->id, $restorable->id]);

        $this->assertSame(1, $result['restored']);
        $this->assertSame([$conflicted->id], $result['failed']);
        $this->assertNotNull(Product::find($restorable->id));
    }
}
