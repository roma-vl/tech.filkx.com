<?php

namespace Tests\Feature\Admin;

use App\Models\Page;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPageControllerTest extends TestCase
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

    public function test_index_returns_a_plain_list_of_pages_not_the_paginators_internals(): void
    {
        // The static_pages migration seeds a handful of default pages itself, so
        // this asserts the created page is *among* the results rather than
        // assuming an exact count.
        $admin = $this->makeAdmin();
        $slug = 'page-'.uniqid();
        $page = Page::create([
            'slug' => $slug,
            'title' => ['uk' => 'Заголовок', 'en' => 'Title'],
            'content' => ['uk' => 'Текст', 'en' => 'Text'],
            'status' => 'published',
        ]);

        $response = $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/admin/pages?per_page=100');

        $response->assertOk();
        // Regression guard: same nested-paginator bug as AdminBlogController::posts()
        // (see that test) - PageResource::collection() must wrap $paginator->items(),
        // not the raw paginator.
        $data = $response->json('data.data');
        $this->assertIsArray($data);
        $this->assertSame(array_values($data), $data);
        $ids = array_column($data, 'id');
        $this->assertContains($page->id, $ids);
    }
}
