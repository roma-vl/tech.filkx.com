<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminProductSearchControllerTest extends TestCase
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

    private function makeProduct(array $overrides = []): Product
    {
        return Product::create(array_merge([
            'slug' => 'product-'.uniqid(),
            'name' => ['uk' => 'Товар', 'en' => 'Product'],
            'description' => ['uk' => '', 'en' => ''],
            'status' => 'active',
        ], $overrides));
    }

    public function test_non_admin_cannot_search_products(): void
    {
        $customer = $this->makeCustomer();

        $this->withHeaders($this->authHeader($customer))
            ->getJson('/api/admin/products/search')
            ->assertForbidden();
    }

    public function test_admin_can_search_paginated_products(): void
    {
        $admin = $this->makeAdmin();
        $active = $this->makeProduct(['status' => 'active']);
        $this->makeProduct(['status' => 'draft']);

        $response = $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/admin/products/search?status=active');

        $response->assertOk();
        $items = collect($response->json('data.items'));
        $this->assertCount(1, $items);
        $this->assertSame($active->id, $items->first()['id']);
        $this->assertSame(1, $response->json('data.meta.total'));
    }

    public function test_search_rejects_an_invalid_status(): void
    {
        $admin = $this->makeAdmin();

        $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/admin/products/search?status=not-a-real-status')
            ->assertStatus(422);
    }

    public function test_non_admin_cannot_fetch_search_ids(): void
    {
        $customer = $this->makeCustomer();

        $this->withHeaders($this->authHeader($customer))
            ->getJson('/api/admin/products/search-ids')
            ->assertForbidden();
    }

    public function test_admin_can_fetch_ids_matching_the_filter(): void
    {
        $admin = $this->makeAdmin();
        $hot = $this->makeProduct(['is_hot' => true]);
        $this->makeProduct(['is_hot' => false]);

        $response = $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/admin/products/search-ids?hot=1');

        $response->assertOk();
        $this->assertSame([$hot->id], $response->json('data.ids'));
    }
}
