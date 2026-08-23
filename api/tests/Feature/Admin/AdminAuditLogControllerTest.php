<?php

namespace Tests\Feature\Admin;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAuditLogControllerTest extends TestCase
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

    private function makeLog(array $overrides = []): AuditLog
    {
        return AuditLog::create(array_merge([
            'action' => 'auth.login',
            'domain' => 'security',
            'message' => 'User logged in',
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
        ], $overrides));
    }

    public function test_non_admin_cannot_list_audit_logs(): void
    {
        $user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->roles()->attach($userRole->id);

        $this->withHeaders($this->authHeader($user))
            ->getJson('/api/admin/logs')
            ->assertForbidden();
    }

    public function test_index_returns_a_flat_paginated_shape_matching_the_frontend(): void
    {
        $admin = $this->makeAdmin();
        $log = $this->makeLog();

        $response = $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/admin/logs');

        $response->assertOk();
        // AdminLogs.vue reads response.data.data as {data: [...], currentPage, lastPage}
        // directly (a flat pagination shape), not {data: [...], meta: {...}}.
        $payload = $response->json('data');
        $this->assertArrayHasKey('currentPage', $payload);
        $this->assertArrayHasKey('lastPage', $payload);
        $this->assertIsArray($payload['data']);
        $this->assertSame($log->id, $payload['data'][0]['id']);
        $this->assertSame('auth.login', $payload['data'][0]['action']);
        $this->assertSame('security', $payload['data'][0]['domain']);
    }

    public function test_index_filters_by_domain(): void
    {
        $admin = $this->makeAdmin();
        $matching = $this->makeLog(['domain' => 'billing']);
        $this->makeLog(['domain' => 'security']);

        $response = $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/admin/logs?domain=billing');

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->all();
        $this->assertSame([$matching->id], $ids);
    }

    public function test_index_filters_by_search(): void
    {
        $admin = $this->makeAdmin();
        $matching = $this->makeLog(['message' => 'Unique needle message']);
        $this->makeLog(['message' => 'Something else']);

        $response = $this->withHeaders($this->authHeader($admin))
            ->getJson('/api/admin/logs?search=needle');

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->all();
        $this->assertSame([$matching->id], $ids);
    }
}
