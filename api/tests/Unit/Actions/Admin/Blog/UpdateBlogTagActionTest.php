<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\UpdateBlogTagAction;
use App\Api\Admin\Dto\BlogTagDto;
use App\Models\BlogTag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateBlogTagActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateBlogTagAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateBlogTagAction::class);
    }

    public function test_execute_updates_the_tags_name_without_touching_the_slug(): void
    {
        $tag = BlogTag::create(['slug' => 'zz-test-original', 'name' => ['uk' => 'О', 'en' => 'O']]);

        $updated = $this->action->execute($tag->id, new BlogTagDto(nameUk: 'Оновлено', nameEn: 'Updated'));

        $this->assertSame('zz-test-original', $updated->slug);
        $this->assertSame(['uk' => 'Оновлено', 'en' => 'Updated'], $updated->name);
    }

    public function test_execute_throws_when_the_tag_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999, new BlogTagDto(nameUk: 'Т', nameEn: 'T'));
    }
}
