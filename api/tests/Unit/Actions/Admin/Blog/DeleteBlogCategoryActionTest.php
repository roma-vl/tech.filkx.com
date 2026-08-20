<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\DeleteBlogCategoryAction;
use App\Models\BlogCategory;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteBlogCategoryActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteBlogCategoryAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteBlogCategoryAction::class);
    }

    public function test_execute_deletes_the_category(): void
    {
        $category = BlogCategory::create(['slug' => 'zz-test-to-delete', 'name' => ['uk' => 'Т', 'en' => 'T']]);

        $this->action->execute($category->id);

        $this->assertDatabaseMissing('blog_categories', ['id' => $category->id]);
    }

    public function test_execute_throws_when_the_category_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999);
    }
}
