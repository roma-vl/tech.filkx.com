<?php

namespace Tests\Feature\Admin;

use App\Models\BlogPost;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminBlogControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        $user = User::factory()->create();
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $user->roles()->attach($adminRole->id);

        return $user;
    }

    private function authHeader(User $user): array
    {
        $token = $user->createToken('api-access')->accessToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_posts_returns_a_plain_list_of_posts_not_the_paginators_internals(): void
    {
        $admin = $this->makeAdmin();
        $post = BlogPost::create([
            'slug' => 'post-'.uniqid(),
            'title' => ['uk' => 'Заголовок', 'en' => 'Title'],
            'content' => ['uk' => 'Текст', 'en' => 'Text'],
            'status' => 'published',
        ]);

        $response = $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/admin/blog/posts');

        $response->assertOk();
        // Regression guard: AdminBlogPostResource::collection() used to wrap the raw
        // paginator instead of $paginator->items(), which made convertToCamelCase()
        // nest the pagination meta *inside* data.data instead of returning a plain
        // list - PostsTab.vue would then iterate over meta fields as if they were
        // posts and crash on `post.id` for any null one (e.g. next_page_url).
        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertSame(array_values($data), $data);
        $this->assertCount(1, $data);
        $this->assertSame($post->id, $data[0]['id']);
    }
}
