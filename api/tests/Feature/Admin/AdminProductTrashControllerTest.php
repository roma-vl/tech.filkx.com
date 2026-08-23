<?php

namespace Tests\Feature\Admin;

use App\Api\V1\Repositories\ProductRepository;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductTrashControllerTest extends TestCase
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

    private function makeProduct(string $slug): Product
    {
        return Product::create([
            'slug' => $slug,
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ]);
    }

    public function test_non_admin_cannot_list_trashed_products(): void
    {
        $customer = $this->makeCustomer();

        $this->withHeaders($this->authHeader($customer))
            ->getJson('/api/admin/products/trashed')
            ->assertForbidden();
    }

    public function test_admin_can_list_trashed_products(): void
    {
        $admin = $this->makeAdmin();
        $deleted = $this->makeProduct('trashed-product');
        app(ProductRepository::class)->delete($deleted);
        $this->makeProduct('active-product');

        $response = $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/admin/products/trashed');

        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($deleted->id, $ids);
        $this->assertCount(1, $ids);
    }

    public function test_non_admin_cannot_restore_a_product(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct('restorable');
        app(ProductRepository::class)->delete($product);

        $this->withHeaders($this->authHeader($customer))
            ->postJson("/api/admin/products/{$product->id}/restore")
            ->assertForbidden();
    }

    public function test_admin_can_restore_a_trashed_product(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct('restorable');
        app(ProductRepository::class)->delete($product);

        $response = $this->withHeaders($this->authHeader($admin))
            ->postJson("/api/admin/products/{$product->id}/restore");

        $response->assertOk();
        $this->assertNotNull(Product::find($product->id));
    }

    public function test_restoring_a_product_that_is_not_trashed_returns_404(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct('never-deleted');

        $this->withHeaders($this->authHeader($admin))
            ->postJson("/api/admin/products/{$product->id}/restore")
            ->assertNotFound();
    }

    public function test_restoring_a_product_with_a_reclaimed_slug_returns_422(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct('shared-slug');
        app(ProductRepository::class)->delete($product);
        $this->makeProduct('shared-slug');

        $this->withHeaders($this->authHeader($admin))
            ->postJson("/api/admin/products/{$product->id}/restore")
            ->assertStatus(422);
    }

    public function test_non_admin_cannot_bulk_restore_products(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct('restorable');
        app(ProductRepository::class)->delete($product);

        $this->withHeaders($this->authHeader($customer))
            ->postJson('/api/admin/products/bulk-restore', ['ids' => [$product->id]])
            ->assertForbidden();
    }

    public function test_admin_can_bulk_restore_products(): void
    {
        $admin = $this->makeAdmin();
        $first = $this->makeProduct('first');
        $second = $this->makeProduct('second');
        app(ProductRepository::class)->delete($first);
        app(ProductRepository::class)->delete($second);

        $response = $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/admin/products/bulk-restore', ['ids' => [$first->id, $second->id]]);

        $response->assertOk()
            ->assertJsonPath('data.restored', 2)
            ->assertJsonPath('data.failed', []);
        $this->assertNotNull(Product::find($first->id));
        $this->assertNotNull(Product::find($second->id));
    }

    public function test_bulk_restore_requires_a_non_empty_ids_array(): void
    {
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeader($admin))
            ->postJson('/api/admin/products/bulk-restore', ['ids' => []])
            ->assertStatus(422);
    }
}
