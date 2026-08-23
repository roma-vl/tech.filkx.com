<?php

namespace Tests\Feature\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminHomeBannerControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeAdmin(): User
    {
        $user = User::factory()->create();
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $user->roles()->attach($adminRole->id);

        return $user;
    }

    private function makeCustomer(): User
    {
        $user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        return $user;
    }

    private function authHeader(User $user): array
    {
        $token = $user->createToken('api-access')->accessToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_non_admin_cannot_manage_banners(): void
    {
        $customer = $this->makeCustomer();

        $this->withHeaders($this->authHeader($customer))
            ->getJson('/api/admin/home-banners')
            ->assertForbidden();
    }

    public function test_admin_can_create_list_update_and_delete_a_banner(): void
    {
        $admin = $this->makeAdmin();
        $headers = $this->authHeader($admin);

        $createResponse = $this->withHeaders($headers)->postJson('/api/admin/home-banners', [
            'title' => 'Знижки на навушники',
            'subtitle' => 'Тільки цього тижня',
            'imagePath' => 'banners/test.jpg',
            'linkType' => 'category',
            'linkValue' => 'audio',
            'isActive' => true,
            'sortOrder' => 1,
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.title', 'Знижки на навушники')
            ->assertJsonPath('data.linkType', 'category');

        $bannerId = $createResponse->json('data.id');
        $this->assertDatabaseHas('home_banners', ['id' => $bannerId, 'title' => 'Знижки на навушники']);

        $this->withHeaders($headers)
            ->getJson('/api/admin/home-banners')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $updateResponse = $this->withHeaders($headers)->putJson("/api/admin/home-banners/{$bannerId}", [
            'title' => 'Оновлений банер',
            'imagePath' => 'banners/test.jpg',
            'linkType' => 'catalog',
            'isActive' => false,
            'sortOrder' => 5,
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.title', 'Оновлений банер')
            ->assertJsonPath('data.isActive', false);

        $this->withHeaders($headers)
            ->deleteJson("/api/admin/home-banners/{$bannerId}")
            ->assertOk();

        $this->assertDatabaseMissing('home_banners', ['id' => $bannerId]);
    }

    public function test_admin_can_create_a_banner_with_no_overlay_text(): void
    {
        // A banner image can already carry its own baked-in design and copy,
        // so title (and every other overlay field) is optional.
        $admin = $this->makeAdmin();

        $response = $this->withHeaders($this->authHeader($admin))->postJson('/api/admin/home-banners', [
            'imagePath' => 'banners/fully-designed.jpg',
            'linkType' => 'catalog',
        ]);

        $response->assertCreated()->assertJsonPath('data.title', '');

        $this->assertDatabaseHas('home_banners', [
            'id' => $response->json('data.id'),
            'title' => '',
        ]);
    }

    public function test_updating_a_missing_banner_returns_404(): void
    {
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeader($admin))
            ->putJson('/api/admin/home-banners/999999', [
                'title' => 'X',
                'imagePath' => 'banners/x.jpg',
                'linkType' => 'catalog',
            ])
            ->assertNotFound();
    }

    public function test_admin_can_upload_a_banner_image(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();

        $response = $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/admin/home-banners/upload', [
                'image' => UploadedFile::fake()->image('hero.jpg'),
            ]);

        $response->assertOk()->assertJsonStructure(['data' => ['path', 'url']]);

        Storage::disk('public')->assertExists($response->json('data.path'));
    }

    public function test_upload_rejects_non_image_files(): void
    {
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/admin/home-banners/upload', [
                'image' => UploadedFile::fake()->create('not-an-image.txt', 10),
            ])
            ->assertStatus(422);
    }
}
