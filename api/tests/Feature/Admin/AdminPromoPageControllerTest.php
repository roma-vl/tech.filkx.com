<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPromoPageControllerTest extends TestCase
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

    private function makeProduct(): Product
    {
        return Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
    }

    public function test_non_admin_cannot_manage_promo_pages(): void
    {
        $customer = $this->makeCustomer();

        $this->withHeaders($this->authHeader($customer))
            ->getJson('/api/admin/promo-pages')
            ->assertForbidden();
    }

    public function test_admin_can_create_list_update_and_delete_a_promo_page(): void
    {
        $admin = $this->makeAdmin();
        $headers = $this->authHeader($admin);
        $product = $this->makeProduct();

        $createResponse = $this->withHeaders($headers)->postJson('/api/admin/promo-pages', [
            'title' => 'Все для школи',
            'subtitle' => 'До -30%',
            'imagePath' => 'promo-pages/test.jpg',
            'isActive' => true,
            'sortOrder' => 1,
            'productIds' => [$product->id],
        ]);

        $createResponse->assertCreated()
            ->assertJsonPath('data.title', 'Все для школи')
            ->assertJsonPath('data.slug', 'vse-dlia-skoli');

        $promoPageId = $createResponse->json('data.id');
        $this->assertDatabaseHas('promo_pages', ['id' => $promoPageId, 'title' => 'Все для школи']);
        $this->assertDatabaseHas('promo_page_product', [
            'promo_page_id' => $promoPageId,
            'product_id' => $product->id,
        ]);

        $this->withHeaders($headers)
            ->getJson('/api/admin/promo-pages')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.productsCount', 1);

        $updateResponse = $this->withHeaders($headers)->putJson("/api/admin/promo-pages/{$promoPageId}", [
            'title' => 'Оновлена акція',
            'imagePath' => 'promo-pages/test.jpg',
            'isActive' => false,
            'productIds' => [],
        ]);

        $updateResponse->assertOk()
            ->assertJsonPath('data.title', 'Оновлена акція')
            ->assertJsonPath('data.isActive', false)
            // The slug is set once at creation and never changes on update.
            ->assertJsonPath('data.slug', 'vse-dlia-skoli');

        $this->assertDatabaseMissing('promo_page_product', [
            'promo_page_id' => $promoPageId,
            'product_id' => $product->id,
        ]);

        $this->withHeaders($headers)
            ->deleteJson("/api/admin/promo-pages/{$promoPageId}")
            ->assertOk();

        $this->assertDatabaseMissing('promo_pages', ['id' => $promoPageId]);
    }

    public function test_creating_a_promo_page_deduplicates_the_slug(): void
    {
        $admin = $this->makeAdmin();
        $headers = $this->authHeader($admin);

        $payload = [
            'title' => 'Чорна пятниця',
            'imagePath' => 'promo-pages/test.jpg',
            'isActive' => true,
        ];

        $first = $this->withHeaders($headers)->postJson('/api/admin/promo-pages', $payload);
        $second = $this->withHeaders($headers)->postJson('/api/admin/promo-pages', $payload);

        $this->assertSame('corna-piatnicia', $first->json('data.slug'));
        $this->assertSame('corna-piatnicia-1', $second->json('data.slug'));
    }

    public function test_updating_a_missing_promo_page_returns_404(): void
    {
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeader($admin))
            ->putJson('/api/admin/promo-pages/999999', [
                'title' => 'X',
                'imagePath' => 'promo-pages/x.jpg',
            ])
            ->assertNotFound();
    }

    public function test_deleting_a_missing_promo_page_returns_404(): void
    {
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeader($admin))
            ->deleteJson('/api/admin/promo-pages/999999')
            ->assertNotFound();
    }

    public function test_admin_can_upload_a_promo_page_image(): void
    {
        Storage::fake('public');
        $admin = $this->makeAdmin();

        $response = $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/admin/promo-pages/upload', [
                'image' => UploadedFile::fake()->image('promo.jpg'),
            ]);

        $response->assertOk()->assertJsonStructure(['data' => ['path', 'url']]);

        Storage::disk('public')->assertExists($response->json('data.path'));
    }

    public function test_upload_rejects_non_image_files(): void
    {
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/admin/promo-pages/upload', [
                'image' => UploadedFile::fake()->create('not-an-image.txt', 10),
            ])
            ->assertStatus(422);
    }
}
