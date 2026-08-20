<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\CreateBlogTagAction;
use App\Api\Admin\Dto\BlogTagDto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateBlogTagActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateBlogTagAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateBlogTagAction::class);
    }

    public function test_execute_creates_the_tag_with_a_generated_slug(): void
    {
        $tag = $this->action->execute(new BlogTagDto(nameUk: 'Знижки', nameEn: 'Zz Test Sale'));

        $this->assertDatabaseHas('blog_tags', ['id' => $tag->id, 'slug' => 'zz-test-sale']);
        $this->assertSame(['uk' => 'Знижки', 'en' => 'Zz Test Sale'], $tag->name);
    }
}
