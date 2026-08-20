<?php

namespace Tests\Unit\Actions\Admin\Category;

use App\Api\Admin\Actions\Category\DeleteCategoryAction;
use App\Models\Category;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteCategoryAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteCategoryAction::class);
    }

    public function test_execute_deletes_the_category(): void
    {
        $category = Category::create(['slug' => 'zz-test-to-delete', 'name' => ['uk' => 'Т', 'en' => 'T']]);

        $this->action->execute($category->id);

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_execute_throws_when_the_category_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999);
    }
}
