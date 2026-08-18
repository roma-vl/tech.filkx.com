<?php

namespace Tests\Feature\Blog;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\BlogTag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogControllerTest extends TestCase
{
    use RefreshDatabase;

    // The `search` filter uses `ILIKE`, a Postgres-only operator (production DB); the
    // sqlite test DB has no ILIKE support, so that path can't be exercised here — same
    // constraint documented in CatalogControllerTest for its search keyword.

    private function makePost(array $overrides = []): BlogPost
    {
        return BlogPost::create(array_merge([
            'slug' => 'post-'.uniqid(),
            'title' => ['uk' => 'Заголовок '.uniqid(), 'en' => 'Title'],
            'excerpt' => ['uk' => 'Короткий опис', 'en' => 'Excerpt'],
            'content' => ['uk' => 'Текст', 'en' => 'Body'],
            'status' => 'published',
            'published_at' => now(),
        ], $overrides));
    }

    private function makeCategory(array $overrides = []): BlogCategory
    {
        return BlogCategory::create(array_merge([
            'slug' => 'category-'.uniqid(),
            'name' => ['uk' => 'Категорія', 'en' => 'Category'],
        ], $overrides));
    }

    private function makeTag(array $overrides = []): BlogTag
    {
        return BlogTag::create(array_merge([
            'slug' => 'tag-'.uniqid(),
            'name' => ['uk' => 'Тег', 'en' => 'Tag'],
        ], $overrides));
    }

    public function test_index_returns_only_published_posts(): void
    {
        $this->makePost(['status' => 'published']);
        $this->makePost(['status' => 'draft']);
        $this->makePost(['status' => 'archived']);

        $response = $this->getJson('/api/v1/blog/posts');

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.meta.total', 1);
    }

    public function test_index_returns_an_empty_page_when_there_are_no_posts(): void
    {
        $response = $this->getJson('/api/v1/blog/posts');

        $response->assertOk()
            ->assertJsonCount(0, 'data.data')
            ->assertJsonPath('data.meta.total', 0);
    }

    public function test_index_includes_the_post_summary_fields(): void
    {
        $category = $this->makeCategory(['slug' => 'news']);
        $tag = $this->makeTag(['slug' => 'php']);
        $post = $this->makePost(['slug' => 'hello-world', 'blog_category_id' => $category->id]);
        $post->tags()->attach($tag->id);

        $response = $this->getJson('/api/v1/blog/posts');

        $response->assertOk()
            ->assertJsonPath('data.data.0.slug', 'hello-world')
            ->assertJsonPath('data.data.0.category.slug', 'news')
            ->assertJsonPath('data.data.0.tags.0.slug', 'php')
            ->assertJsonMissingPath('data.data.0.content');
    }

    public function test_index_filters_by_category_slug(): void
    {
        $category = $this->makeCategory(['slug' => 'news']);
        $otherCategory = $this->makeCategory(['slug' => 'reviews']);
        $this->makePost(['blog_category_id' => $category->id]);
        $this->makePost(['blog_category_id' => $otherCategory->id]);

        $response = $this->getJson('/api/v1/blog/posts?category=news');

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.category.slug', 'news');
    }

    public function test_index_filters_by_tag_slug(): void
    {
        $tag = $this->makeTag(['slug' => 'php']);
        $taggedPost = $this->makePost();
        $taggedPost->tags()->attach($tag->id);
        $this->makePost();

        $response = $this->getJson('/api/v1/blog/posts?tag=php');

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.slug', $taggedPost->slug);
    }

    public function test_index_respects_the_per_page_parameter(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->makePost();
        }

        $response = $this->getJson('/api/v1/blog/posts?per_page=2');

        $response->assertOk()
            ->assertJsonCount(2, 'data.data')
            ->assertJsonPath('data.meta.total', 3)
            ->assertJsonPath('data.meta.perPage', 2)
            ->assertJsonPath('data.meta.lastPage', 2);
    }

    public function test_index_defaults_to_nine_posts_per_page(): void
    {
        $response = $this->getJson('/api/v1/blog/posts');

        $response->assertOk()->assertJsonPath('data.meta.perPage', 9);
    }

    public function test_show_returns_a_published_post_with_its_content(): void
    {
        $category = $this->makeCategory(['slug' => 'news']);
        $tag = $this->makeTag(['slug' => 'php']);
        $post = $this->makePost([
            'slug' => 'hello-world',
            'blog_category_id' => $category->id,
            'content' => ['uk' => 'Повний текст', 'en' => 'Full body'],
        ]);
        $post->tags()->attach($tag->id);

        $response = $this->getJson('/api/v1/blog/posts/hello-world');

        $response->assertOk()
            ->assertJsonPath('data.slug', 'hello-world')
            ->assertJsonPath('data.content.en', 'Full body')
            ->assertJsonPath('data.category.slug', 'news')
            ->assertJsonPath('data.tags.0.slug', 'php');
    }

    public function test_show_increments_the_view_count(): void
    {
        $post = $this->makePost(['slug' => 'hello-world']);
        $this->assertSame(0, $post->fresh()->views);

        $this->getJson('/api/v1/blog/posts/hello-world')->assertOk()->assertJsonPath('data.views', 1);
        $this->getJson('/api/v1/blog/posts/hello-world')->assertOk()->assertJsonPath('data.views', 2);

        $this->assertSame(2, $post->fresh()->views);
    }

    public function test_show_returns_404_for_an_unknown_slug(): void
    {
        $this->getJson('/api/v1/blog/posts/does-not-exist')->assertStatus(404);
    }

    public function test_show_returns_404_for_a_draft_post(): void
    {
        $this->makePost(['slug' => 'draft-post', 'status' => 'draft']);

        $this->getJson('/api/v1/blog/posts/draft-post')->assertStatus(404);
    }

    public function test_show_returns_404_for_an_archived_post(): void
    {
        $this->makePost(['slug' => 'archived-post', 'status' => 'archived']);

        $this->getJson('/api/v1/blog/posts/archived-post')->assertStatus(404);
    }

    public function test_categories_lists_categories_ordered_by_order_with_published_post_counts(): void
    {
        $second = $this->makeCategory(['slug' => 'second', 'order' => 2]);
        $first = $this->makeCategory(['slug' => 'first', 'order' => 1]);
        $this->makePost(['blog_category_id' => $first->id, 'status' => 'published']);
        $this->makePost(['blog_category_id' => $first->id, 'status' => 'published']);
        $this->makePost(['blog_category_id' => $first->id, 'status' => 'draft']);
        $this->makePost(['blog_category_id' => $second->id, 'status' => 'published']);

        $response = $this->getJson('/api/v1/blog/categories');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'first')
            ->assertJsonPath('data.0.postsCount', 2)
            ->assertJsonPath('data.1.slug', 'second')
            ->assertJsonPath('data.1.postsCount', 1);
    }

    public function test_categories_returns_an_empty_list_when_there_are_no_categories(): void
    {
        $response = $this->getJson('/api/v1/blog/categories');

        $response->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_tags_lists_only_tags_used_on_published_posts_ordered_by_popularity(): void
    {
        $popular = $this->makeTag(['slug' => 'popular']);
        $rare = $this->makeTag(['slug' => 'rare']);
        $unused = $this->makeTag(['slug' => 'unused']);

        $publishedA = $this->makePost(['status' => 'published']);
        $publishedB = $this->makePost(['status' => 'published']);
        $draft = $this->makePost(['status' => 'draft']);

        $publishedA->tags()->attach($popular->id);
        $publishedB->tags()->attach($popular->id);
        $publishedA->tags()->attach($rare->id);
        $draft->tags()->attach($unused->id);

        $response = $this->getJson('/api/v1/blog/tags');

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', 'popular')
            ->assertJsonPath('data.0.postsCount', 2)
            ->assertJsonPath('data.1.slug', 'rare')
            ->assertJsonPath('data.1.postsCount', 1);
    }

    public function test_tags_returns_an_empty_list_when_no_tag_has_a_published_post(): void
    {
        $tag = $this->makeTag();
        $draft = $this->makePost(['status' => 'draft']);
        $draft->tags()->attach($tag->id);

        $response = $this->getJson('/api/v1/blog/tags');

        $response->assertOk()->assertJsonCount(0, 'data');
    }
}
