<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductBulkActionsControllerTest extends TestCase
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

    private function makeProduct(string $status = 'active'): Product
    {
        return Product::create([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => $status,
        ]);
    }

    public function test_non_admin_cannot_bulk_delete_products(): void
    {
        $customer = $this->makeCustomer();
        $product = $this->makeProduct();

        $this->withHeaders($this->authHeader($customer))
            ->deleteJson('/api/admin/products/bulk-delete', ['ids' => [$product->id]])
            ->assertForbidden();
    }

    public function test_admin_can_bulk_delete_products(): void
    {
        $admin = $this->makeAdmin();
        $first = $this->makeProduct();
        $second = $this->makeProduct();

        $response = $this->withHeaders($this->authHeader($admin))
            ->deleteJson('/api/admin/products/bulk-delete', ['ids' => [$first->id, $second->id]]);

        $response->assertOk()->assertJsonPath('data.deleted', 2);
        $this->assertNull(Product::find($first->id));
        $this->assertNull(Product::find($second->id));
    }

    public function test_bulk_delete_requires_a_non_empty_ids_array(): void
    {
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeader($admin))
            ->deleteJson('/api/admin/products/bulk-delete', ['ids' => []])
            ->assertStatus(422);
    }

    public function test_admin_can_bulk_update_product_status(): void
    {
        $admin = $this->makeAdmin();
        $first = $this->makeProduct('draft');
        $second = $this->makeProduct('draft');

        $response = $this->withHeaders($this->authHeader($admin))
            ->putJson('/api/admin/products/bulk-status', [
                'ids' => [$first->id, $second->id],
                'status' => 'active',
            ]);

        $response->assertOk()->assertJsonPath('data.updated', 2);
        $this->assertSame('active', $first->fresh()->status);
        $this->assertSame('active', $second->fresh()->status);
    }

    public function test_bulk_update_status_rejects_an_invalid_status(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct();

        $this->withHeaders($this->authHeader($admin))
            ->putJson('/api/admin/products/bulk-status', [
                'ids' => [$product->id],
                'status' => 'not-a-real-status',
            ])
            ->assertStatus(422);
    }

    public function test_admin_can_bulk_update_product_category(): void
    {
        $admin = $this->makeAdmin();
        $category = Category::create(['slug' => 'cat-'.uniqid(), 'name' => ['uk' => 'К', 'en' => 'C'], 'order' => 0]);
        $product = $this->makeProduct();

        $response = $this->withHeaders($this->authHeader($admin))
            ->putJson('/api/admin/products/bulk-category', [
                'ids' => [$product->id],
                'categoryId' => $category->id,
            ]);

        $response->assertOk()->assertJsonPath('data.updated', 1);
        $this->assertEqualsCanonicalizing(
            [$category->id],
            $product->categories()->pluck('categories.id')->all()
        );
    }

    public function test_bulk_update_category_rejects_an_unknown_category_id(): void
    {
        $admin = $this->makeAdmin();
        $product = $this->makeProduct();

        $this->withHeaders($this->authHeader($admin))
            ->putJson('/api/admin/products/bulk-category', [
                'ids' => [$product->id],
                'categoryId' => 999999,
            ])
            ->assertStatus(422);
    }
}
