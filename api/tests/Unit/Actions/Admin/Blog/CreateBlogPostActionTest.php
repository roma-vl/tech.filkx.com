<?php

namespace Tests\Unit\Actions\Admin\Blog;

use App\Api\Admin\Actions\Blog\CreateBlogPostAction;
use App\Api\Admin\Dto\BlogPostDto;
use App\Models\BlogTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateBlogPostActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateBlogPostAction $action;

    protected function setUp(): void
    {
        parent::setUp();

        $this->action = app(CreateBlogPostAction::class);
    }

    private function makeDto(array $overrides = []): BlogPostDto
    {
        return new BlogPostDto(
            titleUk: $overrides['titleUk'] ?? 'Заголовок',
            titleEn: $overrides['titleEn'] ?? 'Zz Test Post',
            contentUk: $overrides['contentUk'] ?? 'Зміст',
            contentEn: $overrides['contentEn'] ?? 'Content',
            excerptUk: $overrides['excerptUk'] ?? null,
            excerptEn: $overrides['excerptEn'] ?? null,
            status: $overrides['status'] ?? 'draft',
            categoryId: $overrides['categoryId'] ?? null,
            tagIds: $overrides['tagIds'] ?? [],
            coverImage: $overrides['coverImage'] ?? null,
            publishedAt: $overrides['publishedAt'] ?? null,
        );
    }

    public function test_execute_creates_a_post_with_a_generated_slug_and_the_authenticated_user_as_author(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $post = $this->action->execute($this->makeDto());

        $this->assertDatabaseHas('blog_posts', [
            'id' => $post->id,
            'slug' => 'zz-test-post',
            'author_id' => $user->id,
            'status' => 'draft',
        ]);
        $this->assertSame(['uk' => 'Заголовок', 'en' => 'Zz Test Post'], $post->title);
    }

    public function test_execute_sets_published_at_to_now_when_status_is_published_and_no_date_given(): void
    {
        $post = $this->action->execute($this->makeDto(['status' => 'published']));

        $this->assertNotNull($post->published_at);
    }

    public function test_execute_leaves_published_at_null_when_status_is_draft(): void
    {
        $post = $this->action->execute($this->makeDto(['status' => 'draft']));

        $this->assertNull($post->published_at);
    }

    public function test_execute_attaches_the_given_tags(): void
    {
        $tag = BlogTag::create(['slug' => 'zz-test-tag', 'name' => ['uk' => 'Т', 'en' => 'T']]);

        $post = $this->action->execute($this->makeDto(['tagIds' => [$tag->id]]));

        $this->assertTrue($post->tags->contains('id', $tag->id));
    }
}
