<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\UpdateBlogPostAction;
use App\Api\Admin\Dto\BlogPostDto;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateBlogPostActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateBlogPostAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(UpdateBlogPostAction::class);
    }

    private function makePost(array $overrides = []): BlogPost
    {
        return BlogPost::create(array_merge([
            'slug' => 'zz-test-post',
            'title' => ['uk' => 'О', 'en' => 'O'],
            'content' => ['uk' => 'C', 'en' => 'C'],
            'status' => 'draft',
        ], $overrides));
    }

    private function makeDto(array $overrides = []): BlogPostDto
    {
        return new BlogPostDto(
            titleUk: $overrides['titleUk'] ?? 'Оновлено',
            titleEn: $overrides['titleEn'] ?? 'Updated',
            contentUk: $overrides['contentUk'] ?? 'Новий зміст',
            contentEn: $overrides['contentEn'] ?? 'New content',
            excerptUk: $overrides['excerptUk'] ?? null,
            excerptEn: $overrides['excerptEn'] ?? null,
            status: $overrides['status'] ?? 'draft',
            categoryId: $overrides['categoryId'] ?? null,
            tagIds: $overrides['tagIds'] ?? [],
            coverImage: $overrides['coverImage'] ?? null,
            publishedAt: $overrides['publishedAt'] ?? null,
        );
    }

    public function test_execute_updates_the_posts_fields_without_touching_the_slug(): void
    {
        $post = $this->makePost();

        $updated = $this->action->execute($post->id, $this->makeDto());

        $this->assertSame('zz-test-post', $updated->slug);
        $this->assertSame(['uk' => 'Оновлено', 'en' => 'Updated'], $updated->title);
        $this->assertSame(['uk' => 'Новий зміст', 'en' => 'New content'], $updated->content);
    }

    public function test_execute_sets_published_at_to_now_when_transitioning_to_published(): void
    {
        $post = $this->makePost(['status' => 'draft']);

        $updated = $this->action->execute($post->id, $this->makeDto(['status' => 'published']));

        $this->assertNotNull($updated->published_at);
    }

    public function test_execute_keeps_the_existing_published_at_when_already_published(): void
    {
        $publishedAt = now()->subDay()->startOfSecond();
        $post = $this->makePost(['status' => 'published', 'published_at' => $publishedAt]);

        $updated = $this->action->execute($post->id, $this->makeDto(['status' => 'published']));

        $this->assertTrue($updated->published_at->equalTo($publishedAt));
    }

    public function test_execute_syncs_tags_replacing_the_previous_set(): void
    {
        $oldTag = BlogTag::create(['slug' => 'zz-test-old', 'name' => ['uk' => 'Т', 'en' => 'T']]);
        $newTag = BlogTag::create(['slug' => 'zz-test-new', 'name' => ['uk' => 'Т', 'en' => 'T']]);
        $post = $this->makePost();
        $post->tags()->attach($oldTag->id);

        $updated = $this->action->execute($post->id, $this->makeDto(['tagIds' => [$newTag->id]]));

        $this->assertSame([$newTag->id], $updated->tags->pluck('id')->all());
    }

    public function test_execute_throws_when_the_post_does_not_exist(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $this->action->execute(999999, $this->makeDto());
    }
}
