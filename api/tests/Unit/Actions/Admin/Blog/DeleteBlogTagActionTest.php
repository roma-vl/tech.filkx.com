<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\DeleteBlogTagAction;
use App\Models\BlogTag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeleteBlogTagActionTest extends TestCase
{
    use RefreshDatabase;

    private DeleteBlogTagAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(DeleteBlogTagAction::class);
    }

    public function test_execute_deletes_the_tag(): void
    {
        $tag = BlogTag::create(['slug' => 'zz-test-to-delete', 'name' => ['uk' => 'Т', 'en' => 'T']]);

        $this->action->execute($tag->id);

        $this->assertDatabaseMissing('blog_tags', ['id' => $tag->id]);
    }

    public function test_execute_throws_when_the_tag_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999);
    }
}
