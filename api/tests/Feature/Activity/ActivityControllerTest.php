<?php

namespace Tests\Feature\Activity;

use App\Models\User;
use App\Models\UserActivity;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityControllerTest extends TestCase
{
    use RefreshDatabase;

    private function authHeader(User $user): array
    {
        $token = $user->createToken('api-access')->accessToken;

        return ['Authorization' => "Bearer {$token}"];
    }

    private function createActivity(int $userId, array $attributes = []): UserActivity
    {
        return UserActivity::create(array_merge([
            'user_id' => $userId,
            'activity_type' => 'order.placed',
            'subject_type' => 'App\\Models\\Order',
            'subject_id' => 1,
        ], $attributes));
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/activities');

        $response->assertUnauthorized();
    }

    public function test_index_returns_only_the_authenticated_users_activities(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $own = $this->createActivity($user->id);
        $this->createActivity($otherUser->id);

        $response = $this->withHeaders($this->authHeader($user))->getJson('/api/activities');

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $own->id)
            ->assertJsonPath('data.data.0.type', 'order.placed');
    }

    public function test_index_filters_by_type(): void
    {
        $user = User::factory()->create();

        $placed = $this->createActivity($user->id, ['activity_type' => 'order.placed']);
        $this->createActivity($user->id, ['activity_type' => 'review.created']);

        $response = $this->withHeaders($this->authHeader($user))
            ->getJson('/api/activities?type=order.placed');

        $response->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.id', $placed->id);
    }

    public function test_index_orders_activities_by_most_recent_first(): void
    {
        $user = User::factory()->create();

        $older = $this->createActivity($user->id);
        $older->forceFill(['created_at' => now()->subDay()])->save();
        $newer = $this->createActivity($user->id);

        $response = $this->withHeaders($this->authHeader($user))->getJson('/api/activities');

        $response->assertOk();
        $ids = collect($response->json('data.data'))->pluck('id')->all();
        $this->assertSame([$newer->id, $older->id], $ids);
    }
}
